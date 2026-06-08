import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

plt.rcParams.update({
    "font.family": "serif",
    "mathtext.fontset": "cm",
    "text.usetex": False, 
})

import io
import base64

def tex_to_b64(tex_str):    
    fig = plt.figure(figsize=(3, 1))
    fig.text(0.5, 0.5, f"${tex_str}$", size=50, va='center', ha='center', color='black', backgroundcolor='white')
    
    buf = io.BytesIO()
    
    plt.tight_layout()
    plt.savefig(buf, format='png', bbox_inches='tight', pad_inches=0.1, transparent=True)
    plt.close(fig)
    
    buf.seek(0)
    img_b64 = base64.b64encode(buf.read()).decode('utf-8')
    
    return img_b64