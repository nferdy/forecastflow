<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>{{ $po->po_number }}</title>
<style>
 body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1e293b}
 .head{border-bottom:3px solid #4f46e5;padding-bottom:10px;margin-bottom:16px}
 h1{font-size:20px;margin:0;color:#4f46e5} .muted{color:#64748b}
 table{width:100%;border-collapse:collapse} .items th{background:#4f46e5;color:#fff;padding:7px;text-align:left;font-size:10px}
 .items td{padding:7px;border-bottom:1px solid #e2e8f0} .r{text-align:right}
 .total td{font-weight:bold;font-size:12px;border-top:2px solid #4f46e5}
 .box{border:1px solid #e2e8f0;padding:10px;border-radius:4px}
</style></head><body>
<table class="head"><tr>
  <td><h1>PURCHASE ORDER</h1><div class="muted">No: <strong>{{ $po->po_number }}</strong><br>Tanggal: {{ $po->created_at->translatedFormat('d F Y') }}</div></td>
  <td class="r"><strong style="font-size:14px">Vortex Supply Dynamics (VSD Lab)</strong><br><span class="muted">Techno-Park Hub, Blater<br>Kabupaten Purbalingga, Jawa Tengah</span></td>
</tr></table>

<table style="margin-bottom:14px"><tr>
  <td style="width:50%;vertical-align:top"><div class="box"><strong>Kepada (Supplier):</strong><br>{{ $po->supplier->name }}<br>{{ $po->supplier->email }}<br>{{ $po->supplier->phone }}<br>{{ $po->supplier->address }}</div></td>
  <td style="width:4%"></td>
  <td style="vertical-align:top"><div class="box"><strong>Ketentuan:</strong><br>Estimasi lead time: {{ $po->supplier->lead_time_days }} hari kerja<br>Dibuat oleh: {{ $po->creator->name ?? 'Sistem ForecastFlow' }}<br>Status: {{ $po->status_label }}</div></td>
</tr></table>

<table class="items">
  <thead><tr><th style="width:24px">No</th><th>Produk</th><th>SKU</th><th class="r">Qty</th><th class="r">Harga Satuan</th><th class="r">Subtotal</th></tr></thead>
  <tbody>
  @foreach ($po->items as $i => $it)
    <tr><td>{{ $i + 1 }}</td><td>{{ $it->product->name }}</td><td>{{ $it->product->sku }}</td><td class="r">{{ $it->qty }}</td>
        <td class="r">{{ \App\Support\Fmt::rp($it->unit_cost) }}</td><td class="r">{{ \App\Support\Fmt::rp($it->subtotal) }}</td></tr>
  @endforeach
  <tr class="total"><td colspan="5" class="r">TOTAL</td><td class="r">{{ \App\Support\Fmt::rp($po->total) }}</td></tr>
  </tbody>
</table>

@if ($po->notes)<p><strong>Catatan:</strong> {{ $po->notes }}</p>@endif

<table style="margin-top:40px"><tr><td style="width:60%"></td><td class="r">Hormat kami,<br><br><br><br><strong>Admin VSD Lab</strong><br><span class="muted">Vortex Supply Dynamics</span></td></tr></table>
<p class="muted" style="margin-top:30px;font-size:9px">Dokumen ini dibuat otomatis oleh ForecastFlow — Predictive Analytics, Precision Supply Chain.</p>
</body></html>
