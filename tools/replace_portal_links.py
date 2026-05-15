import pathlib
import re

root = pathlib.Path(__file__).resolve().parents[1] / "resources" / "views"
pat = re.compile(
    r"\{\{\s*asset\('assets/css/portal/pages/([^']+)\.css'\)\s*\}\}\?v=\{\{\s*filemtime\(public_path\('assets/css/portal/pages/\1\.css'\)\)\s*\}\}"
)
for f in root.rglob("*.blade.php"):
    t = f.read_text(encoding="utf-8")
    nt = pat.sub(lambda m: "{{ \\App\\Support\\PortalAssets::pageUrl('" + m.group(1) + "') }}", t)
    if nt != t:
        f.write_text(nt, encoding="utf-8")
        print("OK", f.relative_to(root.parent))
