{{-- Formulir upload bukti bayar untuk satu tagihan.
     Dipakai di kartu "Tagihan Bulan Berjalan" dan di kartu "Bayar Tagihan"
     yang dibuka dari riwayat (parameter ?bayar=<id>).
     $tagihanForm: TagihanIuran yang akan dibayar. --}}
@if (in_array($tagihanForm->status, ['belum_bayar', 'terlambat']))
    <form method="POST" action="{{ route('dashboard.iuran.bayar', $tagihanForm->id) }}" enctype="multipart/form-data" class="mt-5 border-t border-neutral-200 pt-5">
        @csrf
        <input type="hidden" name="nominal_dibayar" value="{{ $tagihanForm->nominal }}">
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <div>
                <label for="metode-{{ $tagihanForm->id }}" class="text-sm font-semibold">Metode Pembayaran</label>
                <select id="metode-{{ $tagihanForm->id }}" name="metode" required class="mt-1 block w-full rounded-lg border-neutral-300 focus:border-primary-600 focus:ring-primary-600">
                    <option value="transfer">Transfer</option>
                    <option value="tunai">Tunai (ke Bendahara)</option>
                    <option value="qris">QRIS</option>
                </select>
                @error('metode')<p class="mt-2 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="tanggal_bayar-{{ $tagihanForm->id }}" class="text-sm font-semibold">Tanggal Bayar</label>
                <input id="tanggal_bayar-{{ $tagihanForm->id }}" name="tanggal_bayar" type="date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-lg border-neutral-300 px-3 py-2 text-sm">
                @error('tanggal_bayar')<p class="mt-2 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <label for="bukti-{{ $tagihanForm->id }}" class="mt-4 block text-sm font-semibold">Upload Bukti Bayar</label>
        <p class="mt-1 text-xs text-neutral-600">Format JPG, PNG, atau PDF — maksimal 2 MB. Bayar via transfer / tunai ke Bendahara, lalu unggah buktinya di sini.</p>
        <div class="mt-3 flex flex-col gap-3 sm:flex-row">
            <input id="bukti-{{ $tagihanForm->id }}" type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf" required class="flex-1 rounded-lg border border-neutral-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600">Upload Bukti Bayar</button>
        </div>
        @error('bukti')
            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
        @enderror
    </form>
@endif
