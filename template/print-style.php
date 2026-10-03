<style>
@page { size: A4; margin: 15mm; }
* { box-sizing: border-box; }
body { font-family: Arial, sans-serif !important; font-size: 10pt; line-height: 1.45; color: #222; }
.sk-print-body { margin: 0; background: #f3f4f6; padding: 24px 12px; }
.sk-print-toolbar { max-width: 794px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 13px; }
.sk-print-toolbar button { border: 0; border-radius: 8px; padding: 12px 18px; background: #7b203a; color: #fff; font: inherit; cursor: pointer; }
.sk-paper { width: 100%; max-width: 794px; background: #fff; padding: 40px; margin: 0 auto; box-shadow: 0 3px 12px #0000000d; }
.sk-letterhead { display: flex; align-items: center; justify-content: center; gap: 20px; padding-bottom: 20px; border-bottom: 2px solid #7b203a; text-align: center; }
.sk-letterhead img { width: 64px; height: 64px; object-fit: contain; }
.sk-letterhead h1 { font-size: 17pt; margin: 0 0 6px; }
.sk-letterhead p { font-size: 9pt; margin: 3px 0; }
.sk-document-title { text-align: center; margin: 24px 0; }
.sk-document-title h2 { font-size: 13pt; text-transform: uppercase; letter-spacing: .4px; margin: 0 0 6px; }
.sk-document-title p { margin: 0; color: #555; }
.sk-print-identity { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 22px; margin-bottom: 24px; }
.sk-print-identity div { display: flex; align-items: baseline; gap: 10px; }
.sk-print-identity dt { width: 100px; flex-shrink: 0; font-size: 9pt; color: #555; }
.sk-print-identity dd { margin: 0; font-weight: 600; overflow-wrap: anywhere; }
.sk-print-table { width: 100%; border-collapse: collapse; table-layout: auto; }
.sk-print-table th { text-align: left; background: #f6f1f3; font-weight: 600; }
.sk-print-table th, .sk-print-table td { padding: 9px 8px; border: 1px solid #ddd; vertical-align: top; font-size: 9pt; overflow-wrap: anywhere; }
.sk-print-table small { display: block; font-size: 8pt; color: #555; margin-top: 4px; }
.sk-print-number { text-align: center; }
.sk-print-totals { display: flex; justify-content: space-between; gap: 16px; padding: 14px 0; border-bottom: 1px solid #ddd; }
.sk-signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-top: 30px; text-align: center; break-inside: avoid; }
.sk-signatures p { min-height: 76px; }
.sk-signatures strong { display: block; font-size: 10pt; text-decoration: underline; }
/* Laporan master lama memakai lebar tabel tetap; batasi ke lebar kertas. */
body:not(.sk-print-body) { max-width: 794px; margin: 24px auto !important; padding: 24px !important; }
body:not(.sk-print-body) table { max-width: 100% !important; }
body:not(.sk-print-body) .table_1, body:not(.sk-print-body) .table_2 { width: 100% !important; border-collapse: collapse; }
body:not(.sk-print-body) .table_1 td, body:not(.sk-print-body) .table_2 td, body:not(.sk-print-body) th { padding: 7px 8px !important; border-color: #ccc !important; font-family: Arial, sans-serif !important; }
body:not(.sk-print-body) th { background: #f6f1f3 !important; }
@media (max-width: 600px) {
  .sk-paper { padding: 22px 14px; }
  .sk-print-identity { grid-template-columns: 1fr; }
  .sk-letterhead { gap: 10px; }
  .sk-letterhead h1 { font-size: 14pt; }
  .sk-print-toolbar { align-items: flex-start; flex-direction: column; }
  .sk-print-table th, .sk-print-table td { padding: 6px 4px; font-size: 8pt; }
}
@media print {
  .sk-print-body { background: #fff; padding: 0; }
  .sk-print-toolbar { display: none; }
  .sk-paper { max-width: none; margin: 0; padding: 0; box-shadow: none; }
  .sk-print-identity { grid-template-columns: 1fr 1fr; }
  .sk-print-table thead { display: table-header-group; }
  .sk-print-table tr { break-inside: avoid; }
  body:not(.sk-print-body) { margin: 0 !important; padding: 0 !important; max-width: none; }
  .sk-letterhead, .sk-document-title, .sk-print-identity, .sk-print-totals { break-inside: avoid; }
}
</style>
