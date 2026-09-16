from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

out = Path(r'C:\xampp\htdocs\filRouge\EC04\MyProf\06_preuves\02_tags_et_versions.png')
text = """v1.0.0          Release v1.0.0 REST & NoSQL
v1.0.1          Release v1.0.1 CI/CD
v1.0.2          Release v1.0.2: revert README conflict demo
---
tag v1.0.1
Tagger: MihoubiBoutina <tinamihoubi@gmail.com>
Date:   Mon Sep 7 11:47:23 2026 +0200

Release v1.0.1 CI/CD

commit 24193e9a6e11bcfdff519e220897fd908d35e1b6 (tag: v1.0.1)
Author: MihoubiBoutina <tinamihoubi@gmail.com>
Date:   Mon Sep 7 11:43:00 2026 +0200

    ci: move workflow to repository root"""

img = Image.new('RGB', (1800, 1100), '#0d1117')
draw = ImageDraw.Draw(img)
font = None
for fp in [
    'C:/Windows/Fonts/consola.ttf',
    'C:/Windows/Fonts/consolab.ttf',
    'C:/Windows/Fonts/CascadiaCode.ttf',
    'C:/Windows/Fonts/cour.ttf',
    'C:/Windows/Fonts/lucon.ttf',
]:
    try:
        font = ImageFont.truetype(fp, 30)
        break
    except Exception:
        pass
if font is None:
    font = ImageFont.load_default()

header = Image.new('RGB', (1800, 60), '#161b22')
img.paste(header, (0, 0))
header_draw = ImageDraw.Draw(header)
header_draw.rounded_rectangle((14, 14, 120, 44), radius=10, fill='#2ea043')
header_draw.text((30, 12), 'PowerShell', fill='#ffffff', font=font)

x, y = 40, 90
for line in text.splitlines():
    draw.text((x, y), line, fill='#c9d1d9', font=font)
    y += 40

border = (5, 5, 1795, 1095)
draw.rectangle(border, outline='#30363d', width=3)
img.save(out)
print(f'Created: {out}')
