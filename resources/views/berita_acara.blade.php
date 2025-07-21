<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 120px 50px 80px 50px;
        }

        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            text-align: center;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            font-size: 12px;
            text-align: center;
            color: gray;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        table,
        th,
        td {
            border: 1px solid #999;
        }

        th {
            background-color: #eee;
        }

        th,
        td {
            padding: 6px;
            text-align: left;
        }
    </style>
</head>

<body>

    <header>
        <h2>INSTANSI / KOP SURAT</h2>
        <p><strong>Daftar Penerimaan Barang</strong></p>
    </header>

    <footer>
        Dicetak pada: {{ now()->format('d-m-Y H:i') }}
    </footer>

    <main>
        <table style="width: 100%; border: none; font-size: 13px; margin-bottom: 20px;">
            <tr>
                <td style="width: 25%; padding: 6px 10px"><strong>Nama Pengaju</strong></td>
                <td style="width: 75%; padding: 6px 10px">{{ optional($user->dataDiri)->nama_lengkap ?? '...' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 10px"><strong>NID</strong></td>
                <td style="padding: 6px 10px">{{ $user->NID }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 10px"><strong>Penempatan</strong></td>
                <td style="padding: 6px 10px">{{ $user->penempatan->nama_penempatan }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 10px"><strong>Jabatan</strong></td>
                <td style="padding: 6px 10px">{{ optional($user->dataDiri)->jabatan ?? '...' }}
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Tanggal Permintaan</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengajuan as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->alat->nama_barang }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_permintaan)->format('d-m-Y') }}</td>
                        <td>{{ $item->jumlah }}</td>
                        <td>{{ $item->alat->satuan }}</td>
                        <td>Rp. {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <br><br><br>
        <table style="width: 100%; border: none; font-size: 13px; margin-top: 40px;">
            <tr>
                <td style="width: 50%; text-align: left; border: none; vertical-align: top;">
                    Gresik, {{ \Carbon\Carbon::now()->format('d F Y') }}<br>
                    Mengetahui,<br>
                    <strong>Admin Umum</strong>
                    <div style="min-height: 80px;"></div> <!-- Area kosong untuk tanda tangan/stempel -->
                    <div style="border-bottom: 1px solid #000; width: 60%;">
                        <span style="font-size: 13px;">(NAMA LENGKAP)</span>
                    </div>
                    <span style="font-size: 13px;">NID: </span>
                </td>
                <td style="width: 50%; text-align: left; border: none; vertical-align: top;">
                    Gresik, {{ \Carbon\Carbon::now()->format('d F Y') }}<br>
                    Menyetujui,<br>
                    <strong>{{ $user->penempatan->nama_penempatan }}</strong>
                    <div style="min-height: 80px;"></div> <!-- Area kosong untuk tanda tangan/stempel -->
                    <div style="border-bottom: 1px solid #000; width: 60%;">
                        <span style="font-size: 13px;">
                            {{ optional($user->dataDiri)->nama_lengkap ?? '(NAMA PENERIMA)' }}
                        </span>

                    </div>
                    <span style="font-size: 13px;">NID: {{ $user->NID }}</span>
                </td>
            </tr>
        </table>



    </main>

</body>

</html>
