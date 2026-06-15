<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Certificate Preview</title>
  @include('pdf.partials.certificate_styles')
  <style>
    html, body.certificate-preview-page {
      margin: 0;
      padding: 0;
      background: #e2e8f0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .preview-toolbar {
      position: sticky;
      top: 0;
      z-index: 10000;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 12px 20px;
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      color: #f8fafc;
      border-bottom: 3px solid #76CF1C;
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }

    .preview-toolbar strong {
      color: #76CF1C;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    .preview-toolbar p {
      margin: 4px 0 0;
      font-size: 12px;
      color: #cbd5e1;
      line-height: 1.4;
    }

    .preview-toolbar-actions button {
      background: #334155;
      color: #fff;
      border: 1px solid #475569;
      border-radius: 6px;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }

    .preview-toolbar-actions button:hover {
      background: #475569;
    }

    .preview-certificate-wrap {
      max-width: 920px;
      margin: 24px auto 40px;
      padding: 0 16px;
      user-select: none;
      -webkit-user-select: none;
      position: relative;
    }

    .preview-certificate-wrap .page {
      background: #fff;
      box-shadow: 0 8px 30px rgba(15, 23, 42, 0.12);
    }

    .preview-watermark {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-28deg);
      font-size: clamp(48px, 12vw, 96px);
      font-weight: 800;
      color: rgba(15, 23, 42, 0.05);
      pointer-events: none;
      z-index: 9999;
      letter-spacing: 8px;
      white-space: nowrap;
    }

    @media print {
      html, body, .preview-toolbar, .preview-certificate-wrap, .preview-watermark {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        overflow: hidden !important;
      }
    }
  </style>
</head>

<body class="certificate-preview-page" oncontextmenu="return false;">
  <div class="preview-toolbar">
    <div>
      <strong>Preview Only</strong>
      <p>Printing and downloading are disabled. Save and generate the certificate to get the official PDF.</p>
    </div>
    <div class="preview-toolbar-actions">
      <button type="button" onclick="window.close()">Close Preview</button>
    </div>
  </div>

  <div class="preview-watermark" aria-hidden="true">PREVIEW</div>

  <div class="preview-certificate-wrap">
    @include('pdf.partials.certificate_content')
  </div>

  <script>
    (function() {
      function blockAction(event) {
        event.preventDefault();
        event.stopPropagation();
        return false;
      }

      document.addEventListener('keydown', function(event) {
        var key = (event.key || '').toLowerCase();
        if ((event.ctrlKey || event.metaKey) && (key === 'p' || key === 's')) {
          blockAction(event);
        }
      });

      window.addEventListener('beforeprint', blockAction);

      document.addEventListener('dragstart', blockAction);
    })();
  </script>
</body>

</html>
