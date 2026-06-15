<style>
  @page { margin: 8px 10px; }

  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 8.5px;
    color: #1a1a1a;
    margin: 0;
    padding: 0;
  }

  .page {
    border: 2px solid #76CF1C;
    padding: 0;
  }

  /* ── Header ───────────────────────────────────────────── */
  .header {
    background: #15803d;
    color: #ffffff;
    padding: 5px 10px;
  }
  .header table { width: 100%; border-collapse: collapse; }
  .company {
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.3px;
    margin: 0;
  }
  .cert-title {
    font-size: 8.5px;
    margin: 1px 0 0 0;
    color: #eafad9;
    line-height: 1.2;
  }
  .qr-cell { text-align: right; width: 60px; vertical-align: top; }
  .qr {
    width: 50px; height: 50px;
    background: #fff; border: 2px solid #fff;
  }

  .meta-bar {
    background: #eef7e0;
    border-bottom: 1px solid #cfe3b5;
    padding: 3px 10px;
    font-size: 8px;
  }
  .meta-bar table { width: 100%; }
  .meta-bar .bold { font-weight: bold; }

  .content { padding: 5px 10px 4px 10px; }

  .intro {
    line-height: 1.3;
    margin-bottom: 4px;
    text-align: justify;
    font-size: 8px;
  }

  /* ── Section ──────────────────────────────────────────── */
  .section { margin-top: 3px; }
  .section-title {
    background: #15803d;
    color: #fff;
    font-weight: bold;
    font-size: 8.5px;
    padding: 2px 6px;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    margin: 0;
  }
  table.detail {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #cfe3b5;
    border-top: none;
    margin: 0;
  }
  table.detail td {
    border: 1px solid #cfe3b5;
    padding: 2px 5px;
    vertical-align: top;
    font-size: 8px;
  }
  td.label {
    background: #eef7e0;
    font-weight: bold;
    width: 22%;
    color: #3f6f0e;
  }
  td.value { width: 28%; }

  /* ── Images ───────────────────────────────────────────── */
  table.images { width: 100%; border-collapse: collapse; margin: 3px 0 0 0; }
  table.images td {
    text-align: center;
    vertical-align: middle;
    padding: 4px;
    border: 1px solid #cfe3b5;
  }
  .img-frame {
    width: 100%;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
  }
  .img-frame img {
    max-width: 95%;
    max-height: 115px;
    object-fit: contain;
  }
  .img-cap {
    font-size: 7.5px;
    font-weight: bold;
    color: #3f6f0e;
    margin: 3px 0 0 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .img-missing {
    color: #9aa7b4;
    font-size: 7.5px;
    font-style: italic;
  }

  /* ── SIM table ────────────────────────────────────────── */
  table.sim { width: 100%; border-collapse: collapse; border: 1px solid #cfe3b5; border-top: none; margin: 0; }
  table.sim th, table.sim td { border: 1px solid #cfe3b5; padding: 2px 5px; text-align: left; font-size: 7.5px; }
  table.sim th { background: #eef7e0; color: #3f6f0e; font-weight: bold; }
  table.sim td.sim-id {
    font-family: DejaVu Sans Mono, monospace;
    font-size: 8.5px;
    font-weight: bold;
    color: #0f172a;
    letter-spacing: 0.4px;
  }

  /* ── Footer ───────────────────────────────────────────── */
  .footer {
    margin-top: 4px;
    padding: 0 3px;
  }
  .footer table { width: 100%; }
  .sign-box { text-align: right; vertical-align: bottom; }
  .sign-line { border-top: 1px solid #1a1a1a; width: 140px; margin-left: auto; padding-top: 1px; }
  .note { margin-top: 3px; font-size: 7px; color: #6b7785; text-align: center; }
  .bold { font-weight: bold; }
  .u { text-decoration: underline; }
</style>
