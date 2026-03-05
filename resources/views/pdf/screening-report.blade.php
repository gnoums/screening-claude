<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #1a1a2e;
        padding: 30px 40px;
        line-height: 1.5;
    }
    .header {
        border-bottom: 3px solid #5a7123;
        padding-bottom: 16px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .psychologist-block { text-align: right; font-size: 10px; color: #555; }
    .section-title {
        background: #5a7123;
        color: white;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: bold;
        margin: 18px 0 8px;
        border-radius: 3px;
    }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    th {
        background: #e6edcf;
        padding: 6px 10px;
        text-align: left;
        font-size: 10px;
        color: #333;
    }
    td { padding: 5px 10px; border-bottom: 1px solid #e4e4e4; font-size: 10px; }
    .score-box {
        border: 2px solid;
        border-radius: 6px;
        padding: 10px 16px;
        margin: 10px 0;
        display: inline-block;
    }
    .score-label { font-size: 18px; font-weight: bold; }
    .interpretation { font-size: 10px; margin-top: 4px; color: #444; }
    .chart-wrap {
        margin: 12px 0 16px;
        padding: 10px 12px 6px;
        background: #fafafa;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }
    .chart-title {
        font-size: 9px;
        color: #666;
        margin-bottom: 6px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .disclaimer {
        border: 1px solid #f0ad4e;
        background: #fff8e7;
        padding: 10px 14px;
        margin-top: 24px;
        border-radius: 4px;
        font-size: 9px;
        color: #7a5c00;
    }
    .footer {
        border-top: 1px solid #ccc;
        margin-top: 30px;
        padding-top: 8px;
        font-size: 9px;
        color: #999;
        text-align: center;
    }
    .answer-row td:first-child { color: #555; width: 55%; }
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
    }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

{{-- ===== VERSIÓN EN INGLÉS ===== --}}
@include('pdf._screening-body', ['lang' => 'en'])

{{-- ===== SALTO DE PÁGINA ===== --}}
<div class="page-break"></div>

{{-- ===== VERSIÓN EN ESPAÑOL ===== --}}
@include('pdf._screening-body', ['lang' => 'es'])

</body>
</html>
