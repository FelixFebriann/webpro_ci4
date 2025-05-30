<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
<div class="row">
    <ol class="breadcrumb">
        <li><a href="#"><span class="glyphicon glyphicon-home"></span></a></li>
        <li class="active">Master Data Buku</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <h3>Master Data Buku
                    <a href="<?= base_url('admin/input-buku'); ?>"><button type="button" class="btn btn-sm btn-primary pull-right">Input Data Buku</button></a>
                </h3>
                <hr />
                <table data-toggle="table" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" data-pagination="true" data-sort-name="name" data-sort-order="desc">
                    <thead>
                        <tr>
                            <th data-sortable="true">No</th>
                            <th data-sortable="true">Cover Buku</th>
                            <th data-sortable="true">Judul Buku</th>
                            <th data-sortable="true">Pengarang</th>
                            <th data-sortable="true">Penerbit</th>
                            <th data-sortable="true">Tahun</th>
                            <th data-sortable="true">Jumlah Eksemplar</th>
                            <th data-sortable="true">Kategori Buku</th>
                            <th data-sortable="true">Keterangan</th>
                            <th data-sortable="true">Rak</th>
                            <th data-sortable="true">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 0;
                        foreach($dataBuku as $buku){
                        ?>
                        <tr>
                            <td><?= ++$no; ?></td>
                            <td><img src="<?= base_url('Assets/CoverBuku/' . $buku['cover_buku']); ?>" width="50" alt="Cover Buku"></td>
                            <td><?= $buku['judul_buku']; ?></td>
                            <td><?= $buku['pengarang']; ?></td>
                            <td><?= $buku['penerbit']; ?></td>
                            <td><?= $buku['tahun']; ?></td>
                            <td><?= $buku['jumlah_eksemplar']; ?></td>
                            <td><?= $buku['nama_kategori']; ?></td>
                            <td><?= $buku['keterangan']; ?></td>
                            <td><?= $buku['id_rak']; ?></td>
                            <td>
                                <a href="<?= base_url('admin/edit-buku/'.sha1($buku['id_buku'])); ?>">
                                    <button type="button" class="btn btn-sm btn-success">Edit</button>
                                </a>
                                <a href="#" onclick="doDelete('<?= sha1($buku['id_buku']); ?>')">
                                    <button type="button" class="btn btn-sm btn-danger">Hapus</button>
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!--/.row-->
</div><!--/.main-->

<script type="text/javascript">
    function doDelete(idDelete){
        swal({
            title : "Hapus Data Buku?",
            text : "Data ini akan terhapus secara permanen!!",
            icon : "warning",
            buttons : true,
            dangerMode : true,
        })
        .then(ok => {
            if(ok){
                window.location.href = '<?= base_url();?>/admin/hapus-buku/' + idDelete;
            }
        })
    }
</script>