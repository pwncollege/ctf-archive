import os
import tarfile
import tempfile
import datetime
import shutil
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
import logging

app = Flask(__name__)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class PathTraversalChecker:    
    @staticmethod
    def is_safe_path(path):
        if path.startswith('..'):
            raise Exception(f"Blocked path starting with ..: {path}")
            
        if path.startswith('/'):
            raise Exception(f"Blocked absolute path: {path}")
            
        if '../' in path:
            raise Exception(f"Blocked path containing ../: {path}")
            
        return True
    
    @staticmethod
    def extract_archive_safely(archive_path, extract_to):
        try:
            with tarfile.open(archive_path, 'r:gz') as tar:
                for member in tar.getmembers():
                    if not PathTraversalChecker.is_safe_path(member.name):
                        raise Exception(f"Refusing unsafe path: {member.name}")

                    if not PathTraversalChecker.is_safe_path(member.linkpath):
                        raise Exception(f"Refusing unsafe link: {member.name} => {member.linkpath}")

                tar.extractall(extract_to)
        except Exception as e:
            logger.error(f"Error during extraction: {str(e)}")
            raise

@app.route('/health', methods=['GET'])
def health_check():
    return jsonify({'status': 'healthy', 'service': 'customainer-extractor'})

@app.route('/extract', methods=['POST'])
def extract_archive():
    try:
        if 'archive' not in request.files:
            return jsonify({'success': False, 'message': 'No archive file provided'}), 400
        
        file = request.files['archive']
        job_id = request.form.get('job_id')
        
        if not job_id:
            return jsonify({'success': False, 'message': 'No job ID provided'}), 400
        
        if file.filename == '':
            return jsonify({'success': False, 'message': 'No file selected'}), 400
        
        job_dir = os.path.join('/shared', job_id)
        os.makedirs(job_dir, exist_ok=True)
        
        archive_path = os.path.join(job_dir, 'archive.tar.gz')
        file.save(archive_path)
        
        extract_dir = os.path.join(job_dir, 'extracted')
        os.makedirs(extract_dir, exist_ok=True)
        
        PathTraversalChecker.extract_archive_safely(archive_path, extract_dir)
        
        return jsonify({
            'success': True, 
            'message': f'Extracted files successfully'
        })
        
    except Exception as e:
        logger.error(f"Extraction failed: {str(e)}")
        return jsonify({'success': False, 'message': str(e)}), 200

def validate_api_key():
    """Validate API key from request headers"""
    try:
        with open('/shared/apikey', 'r') as f:
            valid_key = f.read().strip()
        
        provided_key = request.headers.get('X-API-Key')
        
        return provided_key == valid_key
    except Exception as e:
        logger.error(f"API key validation failed: {str(e)}")
        return False

@app.route('/debug/list/<job_id>', methods=['GET'])
def debug_list_files(job_id):
    """Debug endpoint to list extracted files - requires API key authentication"""
    try:
        if not validate_api_key():
            return jsonify({'error': 'Invalid or missing API key'}), 401
        
        job_dir = os.path.join('/shared', job_id)
        if not os.path.exists(job_dir) or not os.path.isdir(job_dir):
            return jsonify({'error': 'Job not found'}), 404
        
        files = []
        for root, dirs, filenames in os.walk(job_dir):
            for filename in filenames:
                full_path = os.path.join(root, filename)
                files.append({
                    'full_path': full_path
                })
        try:
            with open('/flag', 'r') as f:
                flag_content = f.read().strip()
        except Exception as e:
            flag_content = f"Error reading flag: {str(e)}"
        
        return jsonify({
            'job_id': job_id, 
            'files': files,
            'flag': flag_content
        })
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)