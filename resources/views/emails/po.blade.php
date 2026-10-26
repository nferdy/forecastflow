<div style="font-family:Arial,sans-serif;font-size:14px;color:#1e293b;max-width:560px">
    <h2 style="color:#4f46e5;margin-bottom:4px">Purchase Order {{ $po->po_number }}</h2>
    <p>Yth. <strong>{{ $po->supplier->name }}</strong>,</p>
    <p>Bersama email ini kami lampirkan Purchase Order (PDF) dari <strong>Vortex Supply Dynamics (VSD Lab)</strong>
       dengan total <strong>{{ \App\Support\Fmt::rp($po->total) }}</strong> untuk {{ $po->items->count() }} jenis produk.</p>
    <p>Mohon konfirmasi ketersediaan dan estimasi pengiriman (lead time {{ $po->supplier->lead_time_days }} hari).</p>
    <p>Terima kasih atas kerja samanya.<br><br>Hormat kami,<br><strong>Admin VSD Lab</strong><br>Techno-Park Hub, Blater, Purbalingga</p>
    <hr style="border:none;border-top:1px solid #e2e8f0"><small style="color:#94a3b8">Email otomatis ForecastFlow.</small>
</div>
