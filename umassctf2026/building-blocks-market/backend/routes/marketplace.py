from flask import Blueprint, render_template, request, redirect, url_for, current_app
from flask_login import login_required, current_user
from models import db, Product

marketplace_bp = Blueprint('marketplace', __name__)


@marketplace_bp.route('/marketplace')
@login_required
def marketplace():
    products = Product.query.filter_by(is_public=True).all()
    return render_template('marketplace.html', products=products, user=current_user)


@marketplace_bp.route('/flag')
@login_required
def flag():
    has_public_listing = Product.query.filter_by(is_public=True).first() is not None
    if not has_public_listing:
        return "No flag for you :(", 200

    flag_value = current_app.config.get('FLAG')
    return flag_value, 200


@marketplace_bp.route('/product/<int:product_id>')
@login_required
def product_detail(product_id):
    product = Product.query.get_or_404(product_id)
    return render_template('product_detail.html', product=product)


@marketplace_bp.route('/sell', methods=['GET', 'POST'])
@login_required
def sell():
    if request.method == 'POST':
        name = request.form.get('name', '').strip()
        description = request.form.get('description', '').strip()
        price = request.form.get('price', '').strip()
        image_url = request.form.get('image_url', '').strip()

        if not name or not price:
            error = 'Product name and price are required'
            return render_template('sell.html', error=error), 400
        
        try:
            price_float = float(price)
            if price_float < 0:
                raise ValueError("Price cannot be negative")
        except (ValueError, TypeError):
            error = 'Price must be a valid number'
            return render_template('sell.html', error=error), 400
        
        product = Product(
            name=name,
            description=description or None,
            price=price_float,
            image_url=image_url or None,
            user_id=current_user.id,
            is_public=False
        )
        
        try:
            db.session.add(product)
            db.session.commit()
            return redirect(url_for('marketplace.product_detail', product_id=product.id))
        except Exception as e:
            db.session.rollback()
            error = f'Failed to create product: {str(e)}'
            return render_template('sell.html', error='Failed to create product'), 500
    
    return render_template('sell.html')
