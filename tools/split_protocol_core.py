"""Extract shared protocol CSS into components/protocol-core.css."""
from pathlib import Path

pages = Path("public/assets/css/portal/pages")
components = Path("public/assets/css/portal/components")
components.mkdir(parents=True, exist_ok=True)

pt_lines = (pages / "protocol-packet-types.css").read_text(encoding="utf-8").splitlines(keepends=True)
# Shared block: lines 2-627 (1-based) -> indices 1:627
core_body = "".join(pt_lines[1:627])
core_path = components / "protocol-core.css"
core_path.write_text(
    "/* Shared protocol module — imported by protocol-* page stylesheets */\n" + core_body,
    encoding="utf-8",
)

import_line = "@import url('../components/protocol-core.css');\n\n"

# protocol-packet-types: replace shared with import + page tail
page_tail_pt = "".join(pt_lines[627:])
(pages / "protocol-packet-types.css").write_text(
    "/* protocol/packet_types — page-specific (core via @import) */\n" + import_line + page_tail_pt,
    encoding="utf-8",
)

# protocol-alerts-index
al_lines = (pages / "protocol-alerts-index.css").read_text(encoding="utf-8").splitlines(keepends=True)
page_tail_al = "".join(al_lines[627:])
(pages / "protocol-alerts-index.css").write_text(
    "/* protocol/alerts/index — page-specific (core via @import) */\n" + import_line + page_tail_al,
    encoding="utf-8",
)

# protocol-index: pre (0-98) + import + tail (726+)
ix_lines = (pages / "protocol-index.css").read_text(encoding="utf-8").splitlines(keepends=True)
pre = "".join(ix_lines[0:99])
tail = "".join(ix_lines[726:])
(pages / "protocol-index.css").write_text(
    pre + import_line + "/* protocol/index — page-specific tail */\n" + tail,
    encoding="utf-8",
)

print("Wrote", core_path, "and updated 3 page CSS files")
