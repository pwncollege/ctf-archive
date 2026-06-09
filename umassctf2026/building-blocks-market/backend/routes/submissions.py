from flask import Blueprint, render_template, request, redirect, url_for, jsonify, session, make_response, current_app
from flask_login import login_required, current_user
from models import db, Submission, Product
import secrets
import requests
import threading
import hmac
import hashlib

submissions_bp = Blueprint('submissions', __name__)


def _get_or_create_csrf_token():
    if not current_user.is_authenticated:
        return secrets.token_hex(32)

    secret = str(current_app.config.get('SECRET_KEY', '')).encode('utf-8')
    msg = f"admin:{current_user.get_id()}".encode('utf-8')
    return hmac.new(secret, msg, hashlib.sha256).hexdigest()


def _validate_csrf(form_token: str) -> bool:
    if not current_user.is_authenticated or not form_token:
        return False

    expected = _get_or_create_csrf_token()
    return hmac.compare_digest(expected, form_token)


@submissions_bp.route('/approval/request', methods=['GET', 'POST'])
@login_required
def choose_product_for_approval():
    if request.method == 'POST':
        submission_url = request.form.get('submission_url', '').strip()

        is_valid, error = validate_submission_url(submission_url)
        if not is_valid:
            return render_template('choose_product_for_approval.html', error=error), 400

        product = Product.query.filter_by(user_id=current_user.id).order_by(Product.created_at.desc()).first()
        if not product:
            return render_template(
                'choose_product_for_approval.html',
                error='No listing is available to submit for approval at this time.'
            ), 400

        try:
            submission = Submission(
                user_id=current_user.id,
                product_id=product.id,
                submission_url=submission_url,
                status='pending'
            )
            db.session.add(submission)
            db.session.commit()

            submission_id = submission.id
            trigger_bot_visit(submission_url)

            return redirect(url_for('submissions.submission_success', submission_id=submission_id))
        except Exception as e:
            db.session.rollback()
            return render_template('choose_product_for_approval.html', error='Failed to submit for approval'), 500
    return render_template('choose_product_for_approval.html')



def validate_submission_url(url):
    if not url:
        return False, "URL is required"
    
    if len(url) > 500:
        return False, "URL is too long"
    
    if not url.startswith(('http://', 'https://')):
        return False, "URL must start with http:// or https://"
    
    return True, None


def trigger_bot_visit(submission_url):
    def _visit():
        try:
            requests.post(
                'http://bot:3001/visit',
                json={'url': submission_url},
                timeout=5
            )
        except Exception as e:
            pass
    
    thread = threading.Thread(target=_visit, daemon=True)
    thread.start()

@submissions_bp.route('/request-approval/<int:product_id>', methods=['GET', 'POST'])
@login_required
def request_approval(product_id):
    product = Product.query.get_or_404(product_id)

    if product.user_id != current_user.id:
        return "Unauthorized", 403

    existing_submission = Submission.query.filter_by(
        product_id=product_id,
        status='pending'
    ).first()
    
    if existing_submission:
        return render_template('submission_error.html', 
                             error="This product is already pending approval")
    
    if request.method == 'POST':
        submission_url = request.form.get('submission_url', '').strip()

        is_valid, error = validate_submission_url(submission_url)
        if not is_valid:
            return render_template('request_approval.html', 
                                 product=product, 
                                 error=error), 400
        
        try:
            submission = Submission(
                user_id=current_user.id,
                product_id=product_id,
                submission_url=submission_url,
                status='pending'
            )
            db.session.add(submission)
            db.session.commit()
            
            submission_id = submission.id
            trigger_bot_visit(submission_url)
            
            return redirect(url_for('submissions.submission_success', submission_id=submission_id))
        except Exception as e:
            db.session.rollback()
            return render_template('submission_error.html', 
                                 error="Failed to submit for approval"), 500
    
    return render_template('request_approval.html', product=product)


@submissions_bp.route('/submission/success/<int:submission_id>')
@login_required
def submission_success(submission_id):
    submission = Submission.query.get_or_404(submission_id)

    if submission.user_id != current_user.id:
        return "Unauthorized", 403
    
    return render_template('submission_success.html', submission=submission)


@submissions_bp.route('/submissions')
@login_required
def my_submissions():
    submissions = Submission.query.filter_by(user_id=current_user.id).all()
    return render_template('my_submissions.html', submissions=submissions)


@submissions_bp.route('/approval/api/submissions')
def admin_submissions_api():
    if not current_user.is_authenticated or current_user.username != 'admin':
        return jsonify({"error": "Unauthorized"}), 403

    pending_submissions = Submission.query.filter_by(status='pending').all()
    approved_submissions = Submission.query.filter_by(status='approved').all()
    rejected_submissions = Submission.query.filter_by(status='rejected').all()

    csrf_token = _get_or_create_csrf_token()

    def _serialize(submission):
        return {
            'id': submission.id,
            'product': {
                'id': submission.product.id if submission.product else None,
                'name': submission.product.name if submission.product else None,
                'price': float(submission.product.price) if getattr(submission.product, 'price', None) is not None else None,
                'description': submission.product.description if submission.product else None,
            },
            'user': {
                'id': submission.user.id if submission.user else None,
                'username': submission.user.username if submission.user else None,
            },
            'status': submission.status,
            'submission_url': submission.submission_url,
            'reviewed_by': submission.reviewed_by,
            'admin_notes': submission.admin_notes,
            'created_at': submission.created_at.isoformat() if submission.created_at else None,
            'reviewed_at': submission.reviewed_at.isoformat() if submission.reviewed_at else None,
        }

    payload = {
        'csrf_token': csrf_token,
        'pending': [_serialize(s) for s in pending_submissions],
        'approved': [_serialize(s) for s in approved_submissions],
        'rejected': [_serialize(s) for s in rejected_submissions],
    }

    return jsonify(payload)


@submissions_bp.route('/approval/approve/<int:submission_id>', methods=['POST'])
@login_required
def admin_approve(submission_id):
    if not current_user.is_authenticated:
        return "Unauthorized - must be logged in as admin", 401
    
    if current_user.username != 'admin':
        return "Unauthorized - only admin can approve", 403
    
    submission = Submission.query.get_or_404(submission_id)

    form_token = request.form.get('csrf_token', '')
    if not _validate_csrf(form_token):
        return "Invalid CSRF token", 400

    try:
        submission.status = 'approved'
        submission.reviewed_by = current_user.username
        submission.reviewed_at = db.func.current_timestamp()

        product = Product.query.get(submission.product_id)
        if product:
            product.is_public = True
        
        db.session.commit()
    except Exception as e:
        db.session.rollback()
        return "Error approving submission", 500

    if request.headers.get('Accept') == 'application/json':
        return jsonify({"success": True, "message": "Submission approved"})
    return make_response(redirect('/admin/submissions.html'))


@submissions_bp.route('/approval/reject/<int:submission_id>', methods=['POST'])
@login_required
def admin_reject(submission_id):
    if current_user.username != 'admin':
        return "Unauthorized", 403
    
    submission = Submission.query.get_or_404(submission_id)

    form_token = request.form.get('csrf_token', '')
    if not _validate_csrf(form_token):
        return "Invalid CSRF token", 400
    notes = request.form.get('admin_notes', '').strip()
    
    if not notes:
        return render_template('submission_error.html', 
                             error="Rejection reason is required"), 400
    
    if len(notes) > 500:
        return render_template('submission_error.html', 
                             error="Rejection reason is too long"), 400
    
    try:
        submission.status = 'rejected'
        submission.admin_notes = notes
        submission.reviewed_by = current_user.username
        submission.reviewed_at = db.func.current_timestamp()
        
        db.session.commit()
    except Exception as e:
        db.session.rollback()

    if request.headers.get('Accept') == 'application/json':
        return jsonify({"success": True, "message": "Submission rejected"})
    return make_response(redirect('/admin/submissions.html'))


@submissions_bp.route('/approval/check-submission/<int:submission_id>')
def admin_bot_check(submission_id):
    submission = Submission.query.get_or_404(submission_id)

    if submission.status != 'pending':
        return jsonify({
            "error": "Submission is not pending",
            "status": submission.status
        }), 400
    
    return jsonify({
        "success": True,
        "submission_id": submission.id,
        "product_id": submission.product_id,
        "status": submission.status,
        "message": "Submission URL is accessible. Admin must manually review and approve."
    })
