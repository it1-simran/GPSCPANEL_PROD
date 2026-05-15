#!/usr/bin/env python3
"""
Move <style> blocks from Blade views into public/assets/css/portal/pages/.
Skips: emails/**, mail/**, pdf/**

layouts.apps / layouts.auth: @push('styles') + asset link(s) after @extends
Standalone HTML: <link> before </head>
"""
from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
VIEWS = ROOT / "resources" / "views"
OUT_DIR = ROOT / "public" / "assets" / "css" / "portal" / "pages"

STYLE_OPEN = re.compile(r"<style\b[^>]*>", re.I)
STYLE_CLOSE = re.compile(r"</style>", re.I)
EXTENDS_LAYOUT = re.compile(
    r"@extends\s*\(\s*['\"](layouts\.apps|layouts\.auth)['\"]\s*\)\s*",
    re.I | re.M,
)
EMPTY_PUSH = re.compile(
    r"@push\s*\(\s*['\"]styles['\"]\s*\)\s*@endpush",
    re.I | re.M | re.S,
)
EMPTYISH_PUSH = re.compile(
    r"@push\s*\(\s*['\"]styles['\"]\s*\)\s*(?:<!--[\s\S]*?-->\s*)*\s*@endpush",
    re.I | re.M,
)


def should_skip(rel: Path) -> bool:
    if "emails" in rel.parts or "mail" in rel.parts:
        return True
    if "pdf" in rel.parts:
        return True
    return False


def css_slug(rel: Path) -> str:
    stem = rel.stem
    if stem.endswith(".blade"):
        stem = stem[: -len(".blade")]
    segs = list(rel.parent.parts) + [stem]
    return "-".join(segs).lower().replace("_", "-")


BLADE_COMMENT = re.compile(r"\{\{--[\s\S]*?--\}\}")


def blade_comment_spans(text: str) -> list[tuple[int, int]]:
    return [(m.start(), m.end()) for m in BLADE_COMMENT.finditer(text)]


def inside_spans(pos: int, spans: list[tuple[int, int]]) -> bool:
    return any(a <= pos < b for a, b in spans)


def extract_style_blocks(text: str) -> list[tuple[int, int, str]]:
    blocks: list[tuple[int, int, str]] = []
    spans = blade_comment_spans(text)
    pos = 0
    while True:
        m = STYLE_OPEN.search(text, pos)
        if not m:
            break
        if inside_spans(m.start(), spans):
            pos = m.end()
            continue
        inner_start = m.end()
        mc = STYLE_CLOSE.search(text, inner_start)
        if not mc:
            raise ValueError(f"Unclosed <style> near offset {m.start()}")
        blocks.append((m.start(), mc.end(), text[inner_start : mc.start()]))
        pos = mc.end()
    return blocks


def strip_style_blocks(text: str, blocks: list[tuple[int, int, str]]) -> str:
    if not blocks:
        return text
    parts = []
    cursor = 0
    for start, end, _ in blocks:
        parts.append(text[cursor:start])
        cursor = end
    parts.append(text[cursor:])
    return "".join(parts)


def collapse_blank_lines(text: str) -> str:
    return re.sub(r"\n{5,}", "\n\n\n\n", text)


def strip_empty_pushes(text: str) -> str:
    prev = None
    while prev != text:
        prev = text
        text = EMPTYISH_PUSH.sub("", text)
        text = EMPTY_PUSH.sub("", text)
    return text


def link_tag(stem: str) -> str:
    return (
        f"<link rel=\"stylesheet\" href=\"{{{{ asset('assets/css/portal/pages/{stem}.css') }}}}?v="
        f"{{{{ filemtime(public_path('assets/css/portal/pages/{stem}.css')) }}}}\">"
    )


def build_push_block(stems: list[str]) -> str:
    lines = ["@push('styles')"] + [link_tag(s) for s in stems] + ["@endpush", ""]
    return "\n".join(lines)


def has_any_portal_stem(text: str, stems: list[str]) -> bool:
    return any(f"portal/pages/{s}.css" in text for s in stems)


def inject_push_after_extends(text: str, stems: list[str]) -> str:
    m = EXTENDS_LAYOUT.search(text)
    if not m:
        return text
    if has_any_portal_stem(text, stems):
        return text
    insert_at = m.end()
    return text[:insert_at] + "\n" + build_push_block(stems) + text[insert_at:]


def insert_link_before_head_close(text: str, stems: list[str]) -> str:
    if has_any_portal_stem(text, stems):
        return text
    parts = []
    for s in stems:
        parts.append(
            "    <link rel=\"stylesheet\" href=\"{{ asset('assets/css/portal/pages/"
            + s
            + ".css') }}?v={{ filemtime(public_path('assets/css/portal/pages/"
            + s
            + ".css')) }}\" />\n"
        )
    chunk = "".join(parts)
    low = text.lower()
    hi = low.find("</head>")
    if hi != -1:
        return text[:hi] + chunk + text[hi:]
    return chunk + text


def process_file(path: Path) -> tuple[bool, str]:
    rel = path.relative_to(VIEWS)
    if should_skip(rel):
        return False, "skip-dir"
    text = path.read_text(encoding="utf-8")
    blocks = extract_style_blocks(text)
    if not blocks:
        return False, "no-styles"

    slug = css_slug(rel)
    banner = "/* portal: migrated from " + rel.as_posix() + " */\n"
    combined = banner + "\n\n".join(b[2].strip() for b in blocks) + "\n"
    push_stems = [slug]

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    (OUT_DIR / f"{slug}.css").write_text(combined, encoding="utf-8")

    new_text = strip_style_blocks(text, blocks)
    new_text = strip_empty_pushes(new_text)

    if EXTENDS_LAYOUT.search(new_text):
        new_text = inject_push_after_extends(new_text, push_stems)
    else:
        new_text = insert_link_before_head_close(new_text, push_stems)

    new_text = collapse_blank_lines(new_text)
    path.write_text(new_text, encoding="utf-8")
    return True, slug


def main() -> int:
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    done = 0
    for path in sorted(VIEWS.rglob("*.blade.php")):
        try:
            ok, msg = process_file(path)
            if ok:
                print(f"OK  {path.relative_to(ROOT)} -> {msg}")
                done += 1
        except Exception as e:
            print(f"ERR {path.relative_to(ROOT)}: {e}", file=sys.stderr)
            return 1
    print(f"Migrated {done} files.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
