let jquery_datatable = $("#table1").DataTable({
    responsive: true,
    autoWidth: false,  // Disable automatic column width calculation
    columns: [
        { width: "5%" },    // No column
        { width: "10%" },   // Kebun column
        { width: "10%" },   // Komoditas column
        { width: "5%" },    // Masa column
        { width: "10%" },   // Bulan-Tahun column
        { width: "8%" },    // High Grade column
        { width: "8%" },    // Low Grade column
        { width: "12%" },   // Harga Pokok Kebun column
        { width: "12%" },   // Biaya Umum Kebun column
        { width: "12%" },   // Luar Usaha Kebun column
        { width: "12%" },   // Biaya Umum Kandir column
        { width: "12%" },   // Luar Usaha Kandir column
        { width: "8%" },    // Bunga Beban column
        { width: "8%" },    // Biaya Penjualan column
        { width: "10%" },   // Biaya Total column
        { width: "15%" },   // Harga Jual Rata-Rata column
        { width: "10%" }    // Aksi column
    ]
});

let customized_datatable = $("#table2").DataTable({
    responsive: true,
    pagingType: 'simple',
    dom:
		"<'row'<'col-3'l><'col-9'f>>" +
		"<'row dt-row'<'col-sm-12'tr>>" +
		"<'row'<'col-4'i><'col-8'p>>",
    "language": {
        "info": "Page _PAGE_ of _PAGES_",
        "lengthMenu": "_MENU_ ",
        "search": "",
        "searchPlaceholder": "Search.."
    }
})

const setTableColor = () => {
    document.querySelectorAll('.dataTables_paginate .pagination').forEach(dt => {
        dt.classList.add('pagination-primary')
    })
}
setTableColor()
jquery_datatable.on('draw', setTableColor)