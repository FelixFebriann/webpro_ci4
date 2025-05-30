<?php

namespace App\Controllers;
//load models
use App\Models\M_Admin;
use App\Models\M_Buku;     
use App\Models\M_Kategori; 
use App\Models\M_Rak;      

class Admin extends BaseController
{
    public function login()
    {
        return view('Backend/Login/login');
    }

    public function dashboard()
    {
        if (session()->get('ses_id') == "" or session()->get('ses_user') == "" or session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silahkan login terlebih dahulu!');
?>
            <script>
                document.location = "<?= base_url('admin/login-admin'); ?>";
            </script>
        <?php
        } else {
            echo view('Backend/Template/header');
            echo view('Backend/Template/sidebar');
            echo view('Backend/Login/dashboard_admin');
            echo view('Backend/Template/footer');
        }
    }

    public function autentikasi()
    {
        $modelAdmin = new M_Admin; //proses inisiasi model
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $cekusername = $modelAdmin->getDataAdmin(['username_admin' => $username, 'is_delete_admin' => '0'])->getNumRows();
        if ($cekusername == 0) {
            session()->setFlashData('error', 'Username TIdak Ditemukan!');
        ?>
            <script>
                history.go(-1);
            </script>
            <?php
        } else {
            $dataUser = $modelAdmin->getDataAdmin(['username_admin' => $username, 'is_delete_admin' => '0'])->getRowArray();
            $passwordUser = $dataUser['password_admin'];

            $verifikasiPassword = password_verify($password, $passwordUser);
            if (!$verifikasiPassword) {
                session()->setFlashdata('error', 'Password Tidak Sesuai!');
            ?>
                <script>
                    history.go(-1);
                </script>
            <?php
            } else {
                $dataSession = [
                    'ses_id' => $dataUser['id_admin'],
                    'ses_user' => $dataUser['nama_admin'],
                    'ses_level' => $dataUser['akses_level']
                ];
                session()->set($dataSession);
                session()->setFlashdata('success', 'Login Berhasil!');
            ?>
                <script>
                    document.location = "<?= base_url('admin/dashboard-admin'); ?>";
                </script>
        <?php
            }
        }
    }

    public function logout()
    {
        session()->remove('ses_id');
        session()->remove('ses_user');
        session()->remove('ses_level');
        session()->setFlashdata('info', 'Anda telah keluar dari sistem!');
        ?>
        <script>
            document.location = "<?= base_url('admin/login-admin'); ?>";
        </script>
        <?php
    }

    public function input_data_admin()
    {
        if (session()->get('ses_id') == "" or session()->get('ses_user') == "" or session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
        ?>
            <script>
                document.location = "<?= base_url('admin/login-admin'); ?>";
            </script>
        <?php
        } else {
            echo view('Backend/Template/header');
            echo view('Backend/Template/sidebar');
            echo view('Backend/MasterAdmin/input-admin');
            echo view('Backend/Template/footer');
        }
    }

    public function simpan_data_admin()
    {
        if (session()->get('ses_id') == "" or session()->get('ses_user') == "" or session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
        ?>
            <script>
                document.location = "<?= base_url('admin/login-admin'); ?>";
            </script>
            <?php
        } else {
            $modelAdmin = new M_Admin; // inisiasi

            $nama = $this->request->getPost('nama');
            $username = $this->request->getPost('username');
            $level = $this->request->getPost('level');

            $cekUname = $modelAdmin->getDataAdmin(['username_admin' => $username])->getNumRows();
            if ($cekUname > 0) {
                session()->setFlashdata('error', 'Username sudah digunakan!!');
            ?>
                <script>
                    history.go(-1);
                </script>
            <?php
            } else {
                $hasil = $modelAdmin->autoNumber()->getRowArray();
                if (!$hasil) {
                    $id = "ADM001";
                } else {
                    $kode = $hasil['id_admin'];
                    $noUrut = (int) substr($kode, -3);
                    $noUrut++;
                    $id = "ADM" . sprintf("%03s", $noUrut);
                }

                $datasimpan = [
                    'id_admin' => $id,
                    'nama_admin' => $nama,
                    'username_admin' => $username,
                    'password_admin' => password_hash('pass_admin', PASSWORD_DEFAULT),
                    'akses_level' => $level,
                    'is_delete_admin' => '0',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $modelAdmin->saveDataAdmin($datasimpan);
                session()->setFlashdata('success', 'Data Admin Berhasil Ditambahkan!!');
            ?>
                <script>
                    document.location = "<?= base_url('admin/master-data-admin'); ?>";
                </script>
            <?php
            }
        }
    }

    public function master_data_admin()
    {
        if (session()->get('ses_id') == "" or session()->get('ses_user') == "" or session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            ?>
            <script>
                document.location = "<?= base_url('admin/login-admin'); ?>";
            </script>
        <?php
        } else {
            $modelAdmin = new M_Admin; // inisiasi

            $uri = service('uri');
            $pages = $uri->getSegment(2);
            $dataUser = $modelAdmin->getDataAdmin(['is_delete_admin' => '0', 'akses_level !=' => '1'])->getResultArray();

            $data['pages'] = $pages;
            $data['data_user'] = $dataUser;

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterAdmin/master-data-admin', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    public function edit_data_admin()
    {
        $uri = service('uri');
        $idEdit = $uri->getSegment(3);
        $modelAdmin = new M_Admin;
        // Mengambil data admin dari table admin di database berdasarkan parameter yang dikirimkan
        $dataAdmin = $modelAdmin->getDataAdmin(['sha1(id_admin)' => $idEdit])->getRowArray();
        session()->set(['idUpdate' => $dataAdmin['id_admin']]);

        $page = $uri->getSegment(2);

        $data['page'] = $page;
        $data['web_title'] = "Edit Data Admin";
        $data['data_admin'] = $dataAdmin; // mengirim array data admin ke view

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterAdmin/edit-admin', $data);
        echo view('Backend/Template/footer', $data);
    }

    public function update_data_admin()
    {
        $modelAdmin = new M_Admin();

        $idUpdate = session()->get('idUpdate');
        $nama = $this->request->getPost('nama');
        $level = $this->request->getPost('level');

        if ($nama == "" or $level == "") {
            session()->setFlashdata('error', 'Isian tidak boleh kosong!!');
        ?>
            <script>
                history.go(-1);
            </script>
        <?php
        } else {
            $dataUpdate = [
                'nama_admin' => $nama,
                'akses_level' => $level,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $whereUpdate = ['id_admin' => $idUpdate];

            $modelAdmin->updateDataAdmin($dataUpdate, $whereUpdate);
            session()->remove('idUpdate');
            session()->setFlashdata('success', 'Data Admin Berhasil Diperbaharui!');
        ?>
            <script>
                document.location = "<?= base_url('admin/master-data-admin'); ?>";
            </script>
        <?php
        }
    }

    public function hapus_data_admin()
    {
        $modelAdmin = new M_Admin();

        $uri = service('uri');
        $idHapus = $uri->getSegment(3);

        $dataUpdate = [
            'is_delete_admin' => '1',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $whereUpdate = ['sha1(id_admin)' => $idHapus];
        $modelAdmin->updateDataAdmin($dataUpdate, $whereUpdate);
        session()->setFlashdata('success', 'Data Admin Berhasil Dihapus!');
        ?>
        <script>
            document.location = "<?= base_url('admin/master-data-admin'); ?>";
        </script>
        <?php
    }

    // Awal Modul Buku
public function master_buku()
{
    $modelBuku = new M_Buku;
    // Mengambil data keseluruhan buku dari table buku di database
    $dataBuku = $modelBuku->getDataBukuJoin(['tbl_buku.is_delete_buku' => '0'])->getResultArray();

    $uri = service('uri');
    $page = $uri->getSegment(2);

    $data['page'] = $page;
    $data['web_title'] = "Master Data Buku";
    $data['dataBuku'] = $dataBuku; // mengirim array data buku ke view

    echo view('Backend/Template/header', $data);
    echo view('Backend/Template/sidebar', $data);
    echo view('Backend/MasterBuku/master-data-buku', $data);
    echo view('Backend/Template/footer', $data);
}
public function input_buku()
{
    $modelKategori = new M_Kategori;
    $modelRak = new M_Rak;
    $uri = service('uri');
    $page = $uri->getSegment(2);

    $data['page'] = $page;
    $data['web_title'] = "Input Data Buku";
    $data['data_kategori'] = $modelKategori->getDataKategori(['is_delete_kategori' => '0'])->getResultArray();
    $data['data_rak'] = $modelRak->getDataRak(['is_delete_rak' => '0'])->getResultArray();

    echo view('Backend/Template/header', $data);
    echo view('Backend/Template/sidebar', $data);
    echo view('Backend/MasterBuku/input-buku', $data);
    echo view('Backend/Template/footer', $data);
}
public function simpan_buku()
{
    $modelBuku = new M_Buku;

    $judulBuku = $this->request->getPost('judul_buku');
    $pengarang = $this->request->getPost('pengarang');
    $penerbit = $this->request->getPost('penerbit');
    $tahun = $this->request->getPost('tahun');
    $jumlahEksemplar = $this->request->getPost('jumlah_eksemplar');
    $kategoriBuku = $this->request->getPost('kategori_buku');
    $keterangan = $this->request->getPost('keterangan');
    $rak = $this->request->getPost('rak');

    if(!$this->validate([
        'cover_buku' => 'uploaded[cover_buku]|max_size[cover_buku, 1024]|ext_in[cover_buku,jpg,jpeg,png]',
    ])){
        session()->setFlashdata('error', 'Format file yang diizinkan : jpg, jpeg, png dengan maksimal ukuran 1 MB');
        return redirect()->to('/admin/input-buku')->withInput();
    }

    if(!$this->validate([
        'e_book' => 'uploaded[e_book]|max_size[e_book, 10240]|ext_in[e_book,pdf]',
    ])){
        session()->setFlashdata('error', 'Format file yang diizinkan : pdf dengan maksimal ukuran 10 MB');
        return redirect()->to('/admin/input-buku')->withInput();
    }
$coverBuku = $this->request->getFile('cover_buku');
$ext1 = $coverBuku->getClientExtension();
$namaFile1 = "Cover-Buku-" . date("ymdhis") . "." . $ext1;
$coverBuku->move('Assets/CoverBuku', $namaFile1);

$eBook = $this->request->getFile('e_book');
$ext2 = $eBook->getClientExtension();
$namaFile2 = "E-Book-" . date("ymdhis") . "." . $ext2;
$eBook->move('Assets/E-Book', $namaFile2);

$hasil = $modelBuku->autoNumber()->getRowArray();
if (!$hasil) {
    $id = "BKU001";
} else {
    $kode = $hasil['id_buku'];
    $noUrut = (int) substr($kode, -3);
    $noUrut++;
    $id = "BKU" . sprintf("%03s", $noUrut);
}
$dataSimpan = [
    'id_buku' => $id,
    'judul_buku' => ucwords($judulBuku),
    'pengarang' => ucwords($pengarang),
    'penerbit' => ucwords($penerbit),
    'tahun' => $tahun,
    'jumlah_eksemplar' => $jumlahEksemplar,
    'id_kategori' => $kategoriBuku,
    'keterangan' => $keterangan,
    'id_rak' => $rak,
    'cover_buku' => $namaFile1,
    'e_book' => $namaFile2,
    'is_delete_buku' => '0',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$modelBuku->saveDataBuku($dataSimpan);
session()->setFlashdata('success', 'Data Buku Berhasil Diperbaharui!');
?>
<script>
    document.location = "<?= base_url('admin/master-buku');?>";
</script>
<?php
}

public function hapus_buku()
{
    $modelBuku = new M_Buku;

    $uri = service('uri');
    $idHapusHashed = $uri->getSegment(3); // Ambil hash ID dari URL

    // Cari data buku berdasarkan hash ID
    $dataHapus = $modelBuku->getDataBuku(['sha1(id_buku)' => $idHapusHashed])->getRowArray();

    // Cek apakah data ditemukan
    if ($dataHapus) {
        // Hapus file cover jika ada dan file nya benar-benar ada
        if (!empty($dataHapus['cover_buku']) && file_exists(FCPATH . 'Assets/CoverBuku/' . $dataHapus['cover_buku'])) {
            unlink(FCPATH . 'Assets/CoverBuku/' . $dataHapus['cover_buku']); // Perbaiki path
        }
        // Hapus file e-book jika ada dan file nya benar-benar ada
        if (!empty($dataHapus['e_book']) && file_exists(FCPATH . 'Assets/E-Book/' . $dataHapus['e_book'])) {
            unlink(FCPATH . 'Assets/E-Book/' . $dataHapus['e_book']); // Perbaiki path
        }

        // Hapus data dari database berdasarkan hash ID
        $modelBuku->hapusDataBuku(['sha1(id_buku)' => $idHapusHashed]); // Gunakan hash untuk where
        session()->setFlashdata('success', 'Data Buku Berhasil Dihapus!');
    } else {
        // Jika data tidak ditemukan
        session()->setFlashdata('error', 'Data Buku tidak ditemukan untuk dihapus!');
    }

?>
<script>
    document.location = "<?= base_url('admin/master-buku');?>"; // Perbaiki URL redirect
</script>
<?php
}
public function edit_buku($id_buku_hashed = null)
    {
        // Cek session dulu
        if (session()->get('ses_id') == "" or session()->get('ses_user') == "" or session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
        ?>
            <script>
                document.location = "<?= base_url('admin/login-admin'); ?>";
            </script>
        <?php
            return; // Hentikan eksekusi jika belum login
        }

        // Jika id hash tidak ada di URL (meskipun route sudah memvalidasi)
        if ($id_buku_hashed === null) {
            // Seharusnya tidak terjadi karena route, tapi sebagai pengaman
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID Buku tidak valid.');
        }

        $modelBuku = new M_Buku();
        $modelKategori = new M_Kategori(); // Untuk dropdown kategori
        $modelRak = new M_Rak();           // Untuk dropdown rak

        // Ambil data buku berdasarkan ID yang di-hash
        $bukuData = $modelBuku->getDataBuku(['sha1(id_buku)' => $id_buku_hashed])->getRowArray();

        // Jika data buku tidak ditemukan
        if (!$bukuData) {
            session()->setFlashdata('error', 'Data Buku tidak ditemukan!');
            ?> <script> document.location = "<?= base_url('admin/master-buku'); ?>"; </script> <?php
            return;
        }

        // Simpan ID asli ke session untuk proses update (mengikuti pola admin/rak)
        session()->set(['idUpdateBuku' => $bukuData['id_buku']]);

        // Siapkan data untuk view edit
        $data = [
            'web_title'     => 'Edit Data Buku',
            'data_buku'     => $bukuData, // Kirim data buku ke view
            'data_kategori' => $modelKategori->getDataKategori(['is_delete_kategori' => '0'])->getResultArray(), // Data untuk dropdown
            'data_rak'      => $modelRak->getDataRak(['is_delete_rak' => '0'])->getResultArray(),           // Data untuk dropdown
        ];

        // Tampilkan view form edit
        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterBuku/edit-buku', $data); // Pastikan view ini ada
        echo view('Backend/Template/footer', $data);
    }
    public function update_buku()
    {
        // Cek session dulu
        if (session()->get('ses_id') == "" or session()->get('ses_user') == "" or session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
        ?>
            <script>
                document.location = "<?= base_url('admin/login-admin'); ?>";
            </script>
        <?php
            return; // Hentikan eksekusi jika belum login
        }

        $modelBuku = new M_Buku();

        // Ambil ID dari session yang disimpan saat edit
        $idUpdate = session()->get('idUpdateBuku');

        // Jika ID tidak ada di session
        if (!$idUpdate) {
            session()->setFlashdata('error', 'Sesi update buku tidak valid atau sudah berakhir!');
            ?> <script> document.location = "<?= base_url('admin/master-buku'); ?>"; </script> <?php
            return;
        }

        // Ambil data buku lama untuk cek file
        $bukuLama = $modelBuku->getDataBuku(['id_buku' => $idUpdate])->getRowArray();
        if (!$bukuLama) {
            session()->setFlashdata('error', 'Data buku yang akan diupdate tidak ditemukan!');
            ?> <script> document.location = "<?= base_url('admin/master-buku'); ?>"; </script> <?php
            return;
        }

        // Ambil data dari form POST
        $judulBuku = $this->request->getPost('judul_buku');
        $pengarang = $this->request->getPost('pengarang');
        $penerbit = $this->request->getPost('penerbit');
        $tahun = $this->request->getPost('tahun');
        $jumlahEksemplar = $this->request->getPost('jumlah_eksemplar');
        $kategoriBuku = $this->request->getPost('kategori_buku');
        $keterangan = $this->request->getPost('keterangan');
        $rak = $this->request->getPost('rak');

        // Validasi input dasar (sesuaikan jika perlu)
        if (empty($judulBuku) || empty($pengarang) || empty($penerbit) || empty($tahun) || empty($jumlahEksemplar) || empty($kategoriBuku) || empty($rak) || !is_numeric($tahun) || !is_numeric($jumlahEksemplar)) {
            session()->setFlashdata('error', 'Semua field wajib diisi (kecuali keterangan, cover, dan e-book)!');
            // Redirect kembali ke form edit dengan input lama
            return redirect()->back()->withInput();
        }

        // Siapkan data untuk update
        $dataUpdate = [
            'judul_buku' => ucwords($judulBuku),
            'pengarang' => ucwords($pengarang),
            'penerbit' => ucwords($penerbit),
            'tahun' => $tahun,
            'jumlah_eksemplar' => $jumlahEksemplar,
            'id_kategori' => $kategoriBuku,
            'keterangan' => $keterangan,
            'id_rak' => $rak,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Proses upload cover jika ada file baru
        $coverBukuFile = $this->request->getFile('cover_buku');
        if ($coverBukuFile && $coverBukuFile->isValid() && !$coverBukuFile->hasMoved()) {
            // Validasi file cover
            if (!$this->validate(['cover_buku' => 'max_size[cover_buku,1024]|ext_in[cover_buku,jpg,jpeg,png,JPG,JPEG,PNG]'])) {
                session()->setFlashdata('error', 'Cover: ' . $this->validator->getError('cover_buku'));
                return redirect()->back()->withInput();
            }
            // Hapus cover lama jika ada
            if (!empty($bukuLama['cover_buku']) && file_exists(FCPATH . 'Assets/CoverBuku/' . $bukuLama['cover_buku'])) {
                unlink(FCPATH . 'Assets/CoverBuku/' . $bukuLama['cover_buku']);
            }
            // Pindahkan cover baru
            $namaFileCover = "Cover-Buku-" . date("ymdhis") . "." . $coverBukuFile->getClientExtension();
            $coverBukuFile->move('Assets/CoverBuku', $namaFileCover);
            $dataUpdate['cover_buku'] = $namaFileCover; // Tambahkan ke data update
        }

        // Proses upload e-book jika ada file baru
        $eBookFile = $this->request->getFile('e_book');
        if ($eBookFile && $eBookFile->isValid() && !$eBookFile->hasMoved()) {
            // Validasi file e-book
            if (!$this->validate(['e_book' => 'max_size[e_book,10240]|ext_in[e_book,pdf]'])) {
                session()->setFlashdata('error', 'E-Book: ' . $this->validator->getError('e_book'));
                return redirect()->back()->withInput();
            }
            // Hapus e-book lama jika ada
            if (!empty($bukuLama['e_book']) && file_exists(FCPATH . 'Assets/E-Book/' . $bukuLama['e_book'])) {
                unlink(FCPATH . 'Assets/E-Book/' . $bukuLama['e_book']);
            }
            // Pindahkan e-book baru
            $namaFileEbook = "E-Book-" . date("ymdhis") . "." . $eBookFile->getClientExtension();
            $eBookFile->move('Assets/E-Book', $namaFileEbook);
            $dataUpdate['e_book'] = $namaFileEbook; // Tambahkan ke data update
        }

        // Lakukan update di database
        $whereUpdate = ['id_buku' => $idUpdate];
        $modelBuku->updateDataBuku($dataUpdate, $whereUpdate);

        // Hapus ID dari session dan set flashdata sukses
        session()->remove('idUpdateBuku');
        session()->setFlashdata('success', 'Data Buku Berhasil Diperbaharui!');

        // Redirect ke halaman master buku
        return redirect()->to(base_url('admin/master-buku'));
    }
    // Akhir Modul Buku
    
        // Awal Modul Anggota
        public function master_anggota()
        {
            $modelAnggota = new \App\Models\M_Anggota(); 
            // Mengambil data anggota yang tidak terhapus
            $dataAnggota = $modelAnggota->getDataAnggota(['is_delete_anggota' => '0'])->getResultArray();
    
            $uri = service('uri');
            $page = $uri->getSegment(2);
    
            $data['page'] = $page;
            $data['web_title'] = "Master Data Anggota";
            $data['dataAnggota'] = $dataAnggota;
    
            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterAnggota/master-data-anggota', $data); // Sesuaikan nama view
            echo view('Backend/Template/footer', $data);
        }
    
        public function input_anggota()
        {
            $uri = service('uri');
            $page = $uri->getSegment(2);
    
            $data['page'] = $page;
            $data['web_title'] = "Input Data Anggota";
    
            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterAnggota/input-anggota', $data); // Sesuaikan nama view
            echo view('Backend/Template/footer', $data);
        }
    
        public function simpan_anggota()
        {
            $modelAnggota = new \App\Models\M_Anggota();
    
            // Ambil data sesuai nama field di form input-anggota.php
            $namaAnggota = $this->request->getPost('nama');
            $jenisKelamin = $this->request->getPost('jenis_kelamin');
            $noTelp = $this->request->getPost('no_tlp');
            $alamat = $this->request->getPost('alamat');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            // Validasi dasar (tambahkan jika perlu)
            if (empty($namaAnggota) || empty($jenisKelamin) || empty($noTelp) || empty($alamat) || empty($email) || empty($password)) {
                session()->setFlashdata('error', 'Semua field wajib diisi!');
                return redirect()->back()->withInput();
            }
    
            // Generate ID Anggota (contoh)
            $jenisKelaminEnum = ($jenisKelamin == 'Laki-laki') ? 'L' : 'P';
            $hasil = $modelAnggota->autoNumber()->getRowArray(); // Misal ada fungsi autoNumber() di model
            if (!$hasil) {
                $id = "AGT001";
            } else {
                $kode = $hasil['id_anggota'];
                $noUrut = (int) substr($kode, -3);
                $noUrut++;
                $id = "AGT" . sprintf("%03s", $noUrut);
            }
    
            $dataSimpan = [
                'id_anggota' => $id,
                'nama_anggota' => ucwords($namaAnggota), 
                'jenis_kelamin' => $jenisKelaminEnum,       
                'no_tlp' => $noTelp,
                'alamat' => $alamat,
                'email' => $email,
                'password_anggota' => password_hash($password, PASSWORD_DEFAULT), 
                 'is_delete_anggota' => '0',
                 'created_at' => date('Y-m-d H:i:s'),
                 'updated_at' => date('Y-m-d H:i:s'),
            ];
    
            $modelAnggota->saveDataAnggota($dataSimpan); // Pastikan ada fungsi ini di model
            session()->setFlashdata('success', 'Data Anggota Berhasil Ditambahkan!');
            return redirect()->to(base_url('admin/master-anggota'));
        }
    
        public function edit_anggota($id_anggota_hashed = null)
        {
            if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
                session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
                return redirect()->to(base_url('admin/login-admin'));
            }
    
            if ($id_anggota_hashed === null) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID Anggota tidak valid.');
            }
    
            $modelAnggota = new \App\Models\M_Anggota();
            $anggotaData = $modelAnggota->getDataAnggota(['sha1(id_anggota)' => $id_anggota_hashed])->getRowArray();
    
            if (!$anggotaData) {
                session()->setFlashdata('error', 'Data Anggota tidak ditemukan!');
                return redirect()->to(base_url('admin/master-anggota'));
            }
    
            session()->set(['idUpdateAnggota' => $anggotaData['id_anggota']]);
    
            $data = [
                'web_title' => 'Edit Data Anggota',
                'data_anggota' => $anggotaData,
            ];
    
            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/MasterAnggota/edit-anggota', $data); // Sesuaikan nama view
            echo view('Backend/Template/footer', $data);
        }
    
        public function update_anggota()
        {
            if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
                session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
                return redirect()->to(base_url('admin/login-admin'));
            }
    
            $modelAnggota = new \App\Models\M_Anggota();
            $idUpdate = session()->get('idUpdateAnggota');
    
            if (!$idUpdate) {
                session()->setFlashdata('error', 'Sesi update anggota tidak valid atau sudah berakhir!');
                return redirect()->to(base_url('admin/master-anggota'));
            }
    
            // Ambil data sesuai nama field di form edit-anggota.php
            $namaAnggota = $this->request->getPost('nama');
            $jenisKelamin = $this->request->getPost('jenis_kelamin');
            $noTelp = $this->request->getPost('no_tlp');
            $alamat = $this->request->getPost('alamat');
            $email = $this->request->getPost('email');

            // Validasi dasar (tambahkan jika perlu)
            if (empty($namaAnggota) || empty($jenisKelamin) || empty($noTelp) || empty($alamat) || empty($email)) {
                session()->setFlashdata('error', 'Semua field wajib diisi!');
                return redirect()->back()->withInput();
            }
            $jenisKelaminEnum = ($jenisKelamin == 'Laki-laki') ? 'L' : 'P';
            $dataUpdate = [
                'nama_anggota' => ucwords($namaAnggota), // Update kolom nama_anggota
                'jenis_kelamin' => $jenisKelaminEnum,
                'no_tlp' => $noTelp,
                'alamat' => $alamat,
                 'email' => $email,
                 'updated_at' => date('Y-m-d H:i:s'),
             ];
    
            $whereUpdate = ['id_anggota' => $idUpdate];
            $modelAnggota->updateDataAnggota($dataUpdate, $whereUpdate);
    
            session()->remove('idUpdateAnggota');
            session()->setFlashdata('success', 'Data Anggota Berhasil Diperbarui!');
            return redirect()->to(base_url('admin/master-anggota'));
        }
    
        public function hapus_anggota($id_anggota_hashed = null)
        {
            if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
                session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
                return redirect()->to(base_url('admin/login-admin'));
            }
    
            if ($id_anggota_hashed === null) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID Anggota tidak valid.');
            }
    
            $modelAnggota = new \App\Models\M_Anggota();
    
            $dataUpdate = [
                'is_delete_anggota' => '1',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $whereUpdate = ['sha1(id_anggota)' => $id_anggota_hashed];
    
            $modelAnggota->updateDataAnggota($dataUpdate, $whereUpdate);
            session()->setFlashdata('success', 'Data Anggota Berhasil Dihapus!');
            return redirect()->to(base_url('admin/master-anggota'));
        }
        // Akhir Modul Anggota

    // Awal Modul Kategori
    public function master_kategori()
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        $modelKategori = new \App\Models\M_Kategori(); // Pastikan namespace benar
        $dataKategori = $modelKategori->getDataKategori(['is_delete_kategori' => '0'])->getResultArray();

        $uri = service('uri');
        $page = $uri->getSegment(2);

        $data['page'] = $page;
        $data['web_title'] = "Master Data Kategori";
        $data['dataKategori'] = $dataKategori;

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterKategori/master-data-kategori', $data); // Sesuaikan nama view
        echo view('Backend/Template/footer', $data);
    }

    public function input_kategori()
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        $uri = service('uri');
        $page = $uri->getSegment(2);

        $data['page'] = $page;
        $data['web_title'] = "Input Data Kategori";

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterKategori/input-kategori', $data); // Sesuaikan nama view
        echo view('Backend/Template/footer', $data);
    }

    public function simpan_kategori()
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        $modelKategori = new \App\Models\M_Kategori();

        $namaKategori = $this->request->getPost('nama_kategori');
        // Tambahkan validasi jika diperlukan
        if (empty($namaKategori)) {
            session()->setFlashdata('error', 'Nama Kategori tidak boleh kosong!');
            return redirect()->back()->withInput();
        }

        // Generate ID Kategori (contoh - pastikan autoNumber() ada di model)
        $hasil = $modelKategori->autoNumber()->getRowArray();
        if (!$hasil) {
            $id = "KTG001";
        } else {
            $kode = $hasil['id_kategori'];
            $noUrut = (int) substr($kode, -3);
            $noUrut++;
            $id = "KTG" . sprintf("%03s", $noUrut);
        }

        $dataSimpan = [
            'id_kategori' => $id,
            'nama_kategori' => ucwords($namaKategori),
            'is_delete_kategori' => '0',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modelKategori->saveDataKategori($dataSimpan); // Pastikan ada fungsi ini di model
        session()->setFlashdata('success', 'Data Kategori Berhasil Ditambahkan!');
        return redirect()->to(base_url('admin/master-kategori'));
    }

    public function edit_kategori($id_kategori_hashed = null)
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        if ($id_kategori_hashed === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID Kategori tidak valid.');
        }

        $modelKategori = new \App\Models\M_Kategori();
        $kategoriData = $modelKategori->getDataKategori(['sha1(id_kategori)' => $id_kategori_hashed])->getRowArray();

        if (!$kategoriData) {
            session()->setFlashdata('error', 'Data Kategori tidak ditemukan!');
            return redirect()->to(base_url('admin/master-kategori'));
        }

        session()->set(['idUpdateKategori' => $kategoriData['id_kategori']]);

        $data = [
            'web_title' => 'Edit Data Kategori',
            'data_kategori' => $kategoriData,
        ];

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterKategori/edit-kategori', $data); // Sesuaikan nama view
        echo view('Backend/Template/footer', $data);
    }

    public function update_kategori()
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        $modelKategori = new \App\Models\M_Kategori();
        $idUpdate = session()->get('idUpdateKategori');

        if (!$idUpdate) {
            session()->setFlashdata('error', 'Sesi update kategori tidak valid atau sudah berakhir!');
            return redirect()->to(base_url('admin/master-kategori'));
        }

        $namaKategori = $this->request->getPost('nama_kategori');
        // Tambahkan validasi jika diperlukan
        if (empty($namaKategori)) {
            session()->setFlashdata('error', 'Nama Kategori tidak boleh kosong!');
            return redirect()->back()->withInput();
        }

        $dataUpdate = [
            'nama_kategori' => ucwords($namaKategori),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $whereUpdate = ['id_kategori' => $idUpdate];
        $modelKategori->updateDataKategori($dataUpdate, $whereUpdate);

        session()->remove('idUpdateKategori');
        session()->setFlashdata('success', 'Data Kategori Berhasil Diperbarui!');
        return redirect()->to(base_url('admin/master-kategori'));
    }

    public function hapus_kategori($id_kategori_hashed = null)
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        if ($id_kategori_hashed === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID Kategori tidak valid.');
        }

        $modelKategori = new \App\Models\M_Kategori();

        $dataUpdate = [
            'is_delete_kategori' => '1',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $whereUpdate = ['sha1(id_kategori)' => $id_kategori_hashed];

        $modelKategori->updateDataKategori($dataUpdate, $whereUpdate);
        session()->setFlashdata('success', 'Data Kategori Berhasil Dihapus!');
        return redirect()->to(base_url('admin/master-kategori'));
    }
    // Akhir Modul Kategori

    // Awal Modul Rak
    public function master_rak()
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        $modelRak = new \App\Models\M_Rak(); // Pastikan namespace benar
        $dataRak = $modelRak->getDataRak(['is_delete_rak' => '0'])->getResultArray();

        $uri = service('uri');
        $page = $uri->getSegment(2);

        $data['page'] = $page;
        $data['web_title'] = "Master Data Rak";
        $data['dataRak'] = $dataRak;

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterRak/master-data-rak', $data); // Sesuaikan nama view
        echo view('Backend/Template/footer', $data);
    }

    public function input_rak()
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        $uri = service('uri');
        $page = $uri->getSegment(2);

        $data['page'] = $page;
        $data['web_title'] = "Input Data Rak";

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterRak/input-rak', $data); // Sesuaikan nama view
        echo view('Backend/Template/footer', $data);
    }

    public function simpan_rak()
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        $modelRak = new \App\Models\M_Rak();

        $namaRak = $this->request->getPost('nama_rak');
        // Tambahkan validasi jika diperlukan
        if (empty($namaRak)) {
            session()->setFlashdata('error', 'Nama Rak tidak boleh kosong!');
            return redirect()->back()->withInput();
        }

        // Generate ID Rak (contoh - pastikan autoNumber() ada di model)
        $hasil = $modelRak->autoNumber()->getRowArray();
        if (!$hasil) {
            $id = "RAK001";
        } else {
            $kode = $hasil['id_rak'];
            $noUrut = (int) substr($kode, -3);
            $noUrut++;
            $id = "RAK" . sprintf("%03s", $noUrut);
        }

        $dataSimpan = [
            'id_rak' => $id,
            'nama_rak' => ucwords($namaRak),
            'is_delete_rak' => '0',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $modelRak->saveDataRak($dataSimpan); // Pastikan ada fungsi ini di model
        session()->setFlashdata('success', 'Data Rak Berhasil Ditambahkan!');
        return redirect()->to(base_url('admin/master-rak'));
    }

    public function edit_rak($id_rak_hashed = null)
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        if ($id_rak_hashed === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID Rak tidak valid.');
        }

        $modelRak = new \App\Models\M_Rak();
        $rakData = $modelRak->getDataRak(['sha1(id_rak)' => $id_rak_hashed])->getRowArray();

        if (!$rakData) {
            session()->setFlashdata('error', 'Data Rak tidak ditemukan!');
            return redirect()->to(base_url('admin/master-rak'));
        }

        session()->set(['idUpdateRak' => $rakData['id_rak']]);

        $data = [
            'web_title' => 'Edit Data Rak',
            'data_rak' => $rakData,
        ];

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/MasterRak/edit-rak', $data); // Sesuaikan nama view
        echo view('Backend/Template/footer', $data);
    }

    public function update_rak()
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        $modelRak = new \App\Models\M_Rak();
        $idUpdate = session()->get('idUpdateRak');

        if (!$idUpdate) {
            session()->setFlashdata('error', 'Sesi update rak tidak valid atau sudah berakhir!');
            return redirect()->to(base_url('admin/master-rak'));
        }

        $namaRak = $this->request->getPost('nama_rak');
        // Tambahkan validasi jika diperlukan
        if (empty($namaRak)) {
            session()->setFlashdata('error', 'Nama Rak tidak boleh kosong!');
            return redirect()->back()->withInput();
        }

        $dataUpdate = [
            'nama_rak' => ucwords($namaRak),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $whereUpdate = ['id_rak' => $idUpdate];
        $modelRak->updateDataRak($dataUpdate, $whereUpdate);

        session()->remove('idUpdateRak');
        session()->setFlashdata('success', 'Data Rak Berhasil Diperbarui!');
        return redirect()->to(base_url('admin/master-rak'));
    }

    public function hapus_rak($id_rak_hashed = null)
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to(base_url('admin/login-admin'));
        }

        if ($id_rak_hashed === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID Rak tidak valid.');
        }

        $modelRak = new \App\Models\M_Rak();

        $dataUpdate = [
            'is_delete_rak' => '1',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $whereUpdate = ['sha1(id_rak)' => $id_rak_hashed];

        $modelRak->updateDataRak($dataUpdate, $whereUpdate);
        session()->setFlashdata('success', 'Data Rak Berhasil Dihapus!');
        return redirect()->to(base_url('admin/master-rak'));
    }
    // Akhir Modul Rak
}
