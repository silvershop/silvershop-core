<?php

/**
 * Malay (Malaysia) language pack
 * @package modules: ecommerce
 * @subpackage i18n
 */

i18n::include_locale_file('modules: ecommerce', 'en_US');

global $lang;

if(array_key_exists('ms_MY', $lang) && is_array($lang['ms_MY'])) {
	$lang['ms_MY'] = array_merge($lang['en_US'], $lang['ms_MY']);
} else {
	$lang['ms_MY'] = $lang['en_US'];
}

$lang['ms_MY']['AccountPage_order.ss']['EMAILDETAILS'] = 'Salinan maklumat ini telah dihantar ke alamat emel anda untuk pengesahan perincian pesanan.';
$lang['ms_MY']['Cart.ss']['NOITEMS'] = 'Tiada barang dalam pedati anda';
$lang['ms_MY']['Cart.ss']['PRICE'] = 'Harga';
$lang['ms_MY']['Cart.ss']['READMORE'] = 'Klik sini untuk membaca &quot;%s&quot; dengan lebih lanjut';
$lang['ms_MY']['Cart.ss']['Remove'] = 'Singkirkan &quot;%s&quot; dari pedati anda';
$lang['ms_MY']['Cart.ss']['RemoveAlt'] = 'Singkir';
$lang['ms_MY']['Cart.ss']['SHIPPING'] = 'Kiriman';
$lang['ms_MY']['Cart.ss']['SUBTOTAL'] = 'Jumlah Kecil';
$lang['ms_MY']['Cart.ss']['TOTAL'] = 'Jumlah';
$lang['ms_MY']['CheckoutPage_OrderIncomplete.ss']['CHEQUEINSTRUCTIONS'] = 'Untuk pesanan menggunakan cek, arahan selanjutnya akan dihantar ke e-mail anda.';
$lang['ms_MY']['CheckoutPage_OrderIncomplete.ss']['DETAILSSUBMITTED'] = 'Berikut adalah butiran yang telah dihantar';
$lang['ms_MY']['CheckoutPage_OrderSuccessful.ss']['BACKTOCHECKOUT'] = 'Klik di sini untuk kembali ke Checkout';
$lang['ms_MY']['CheckoutPage_OrderSuccessful.ss']['CHECKOUT'] = 'Checkout';
$lang['ms_MY']['CheckoutPage_OrderSuccessful.ss']['EMAILDETAILS'] = 'Salinan maklumat ini telah dihantar ke alamat emel anda untuk pengesahan perincian pesanan.';
$lang['ms_MY']['CheckoutPage_OrderSuccessful.ss']['ORDERSTATUS'] = 'Status pesanan';
$lang['ms_MY']['CheckoutPage_OrderSuccessful.ss']['PROCESS'] = 'Proses';
$lang['ms_MY']['CheckoutPage_OrderSuccessful.ss']['SUCCESSFULl'] = 'Pesanan selesai';
$lang['ms_MY']['ChequePayment']['MESSAGE'] = 'Bayaran diluluskan melalui Cek. Sila catat: produk-produk tidak akan dikirimkan sehingga bayaran diterima.';
$lang['ms_MY']['FindOrderReport']['DATERANGE'] = 'Julat Tarikh';
$lang['ms_MY']['MemberForm']['DETAILSSAVED'] = 'Butir-butir anda telah disimpan';
$lang['ms_MY']['MemberForm']['LOGGEDIN'] = 'Anda didaftarkan masuk sebagai';
$lang['ms_MY']['OrderInformation.ss']['ADDRESS'] = 'Alamat';
$lang['ms_MY']['OrderInformation.ss']['AMOUNT'] = 'Jumlah';
$lang['ms_MY']['OrderInformation.ss']['BUYERSADDRESS'] = 'Alamt pembeli';
$lang['ms_MY']['OrderInformation.ss']['CITY'] = 'Bandar';
$lang['ms_MY']['OrderInformation.ss']['COUNTRY'] = 'Negara';
$lang['ms_MY']['OrderInformation.ss']['CUSTOMERDETAILS'] = 'Butiran Pelanggan';
$lang['ms_MY']['OrderInformation.ss']['DATE'] = 'Tarikh';
$lang['ms_MY']['OrderInformation.ss']['DETAILS'] = 'Butir-butiran';
$lang['ms_MY']['OrderInformation.ss']['EMAIL'] = 'Emel';
$lang['ms_MY']['OrderInformation.ss']['MOBILE'] = 'Telefon Bimbit';
$lang['ms_MY']['OrderInformation.ss']['NAME'] = 'Nama';
$lang['ms_MY']['OrderInformation.ss']['ORDERSUMMARY'] = 'Rumusan Pesanan';
$lang['ms_MY']['OrderInformation.ss']['PAYMENTID'] = 'ID Bayaran';
$lang['ms_MY']['OrderInformation.ss']['PAYMENTINFORMATION'] = 'Butiran Bayaran';
$lang['ms_MY']['OrderInformation.ss']['PAYMENTMETHOD'] = 'Cara';
$lang['ms_MY']['OrderInformation.ss']['PAYMENTSTATUS'] = 'Taraf Bayaran';
$lang['ms_MY']['OrderInformation.ss']['PHONE'] = 'Telefon';
$lang['ms_MY']['OrderInformation.ss']['PRICE'] = 'Harga';
$lang['ms_MY']['OrderInformation.ss']['PRODUCT'] = 'Produk';
$lang['ms_MY']['OrderInformation.ss']['QUANTITY'] = 'Kuantiti';
$lang['ms_MY']['OrderInformation.ss']['SHIPPING'] = 'Kiriman';
$lang['ms_MY']['OrderInformation.ss']['SHIPPINGTO'] = 'kepada';
$lang['ms_MY']['OrderInformation.ss']['SUBTOTAL'] = 'Jumlah Kecil';
$lang['ms_MY']['OrderInformation.ss']['TOTALl'] = 'Jumlah';
$lang['ms_MY']['OrderInformation.ss']['TOTALPRICE'] = 'Jumlah Harga';
$lang['ms_MY']['OrderInformation_Editable.ss']['ADDONE'] = 'Tambah satu lagi &quot;%s&quot; kedalam bakul anda';
$lang['ms_MY']['OrderInformation_Editable.ss']['NOITEMS'] = '<strong>Tiada</strong> barangan dalam bakul anda.';
$lang['ms_MY']['OrderInformation_Editable.ss']['ORDERINFORMATION'] = 'Maklumat pesanan';
$lang['ms_MY']['OrderInformation_Editable.ss']['PRICE'] = 'Harga';
$lang['ms_MY']['OrderInformation_Editable.ss']['PRODUCT'] = 'Barangan';
$lang['ms_MY']['OrderInformation_Editable.ss']['QUANTITY'] = 'Bilangan';
$lang['ms_MY']['OrderInformation_Editable.ss']['READMORE'] = 'Klik sini untuk membaca &quot;%s&quot; dengan lebih lanjut';
$lang['ms_MY']['OrderInformation_Editable.ss']['REMOVEONE'] = 'Keluarkan salah satu daripada &quot;%s&quot; daripada bakul anda';
$lang['ms_MY']['OrderInformation_Editable.ss']['SHIPPING'] = 'Penghantaran';
$lang['ms_MY']['OrderInformation_Editable.ss']['SHIPPINGTO'] = 'kepada';
$lang['ms_MY']['OrderInformation_Editable.ss']['SUBTOTAL'] = 'Jumlah kecil';
$lang['ms_MY']['OrderInformation_Editable.ss']['TABLESUMMARY'] = 'Kandungan bakul anda dipaparkan menggunakan borang ini, mengandungi maklumat pesanan, bayaran terlibat serta pilihan cara bayaran.';
$lang['ms_MY']['OrderInformation_Editable.ss']['TOTAL'] = 'Jumlah';
$lang['ms_MY']['OrderInformation_Editable.ss']['TOTALPRICE'] = 'Jumlah Harga';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['ADDRESS'] = 'Alamat';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['BUYERSADDRESS'] = 'Alamat pembeli';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['CITY'] = 'Bandar';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['COUNTRY'] = 'Negara';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['CUSTOMERDETAILS'] = 'Maklumat pelanggan';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['EMAIL'] = 'Emel';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['MOBILE'] = 'Telefin Bimbit';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['NAME'] = 'Nama';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['ORDERINFO'] = 'Maklumat untuk # Pesanan';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['PHONE'] = 'Telefon';
$lang['ms_MY']['OrderInformation_NoPricing.ss']['TABLESUMMARY'] = 'Kandungan bakul anda dipaparkan menggunakan borang ini, mengandungi maklumat bayaran terlibat serta pilihan cara bayaran.';
$lang['ms_MY']['OrderInformation_PackingSlip.ss']['DESCRIPTION'] = 'Keterangan';
$lang['ms_MY']['OrderInformation_PackingSlip.ss']['ITEM'] = 'Barangan';
$lang['ms_MY']['OrderInformation_PackingSlip.ss']['ORDERDATE'] = 'Tarikh pesanan';
$lang['ms_MY']['OrderInformation_PackingSlip.ss']['ORDERNUMBER'] = 'Nombor pesanan';
$lang['ms_MY']['OrderInformation_PackingSlip.ss']['PAGETITLE'] = 'Cetak Pesanan-pesanan Kedai';
$lang['ms_MY']['OrderInformation_PackingSlip.ss']['QUANTITY'] = 'Bilangan';
$lang['ms_MY']['OrderInformation_PackingSlip.ss']['TABLESUMMARY'] = 'Kandungan bakul anda dipaparkan menggunakan borang ini, mengandungi maklumat pesanan, bayaran terlibat serta pilihan cara bayaran.';
$lang['ms_MY']['OrderInformation_Print.ss']['PAGETITLE'] = 'Cetak Pesanan-pesanan';
$lang['ms_MY']['OrderReport']['CHANGESTATUS'] = 'Ubah Status Tertib';
$lang['ms_MY']['OrderReport']['NOTEEMAIL'] = 'Nota/Emel';
$lang['ms_MY']['OrderReport']['PRINTEACHORDER'] = 'Cetak semua tertib yang dipamerkan';
$lang['ms_MY']['OrderReport']['SENDNOTETO'] = 'Hantar nota ini kepada %s (%s)';
$lang['ms_MY']['Order_Member.ss']['EMAIL'] = 'Emel';
$lang['ms_MY']['Order_receiptEmail.ss']['HEADLINE'] = 'Resit Pesanan Kedai';
$lang['ms_MY']['Order_receiptEmail.ss']['TITLE'] = 'Resit Kedai';
$lang['ms_MY']['Order_statusEmail.ss']['HEADLINE'] = 'Perubahan Status Kedai';
$lang['ms_MY']['Order_statusEmail.ss']['STATUSCHANGE'] = 'Status telah diubah kepada "%s" untuk pesanan #';
$lang['ms_MY']['Order_statusEmail.ss']['TITLE'] = 'Perubahan Status Kedai';
$lang['ms_MY']['PaymentInformation.ss']['AMOUNT'] = 'Jumlah';
$lang['ms_MY']['PaymentInformation.ss']['DATE'] = 'Tarikh';
$lang['ms_MY']['PaymentInformation.ss']['PAYMENTID'] = 'ID Bayaran';
$lang['ms_MY']['PaymentInformation.ss']['PAYMENTINFORMATION'] = 'Maklumat Bayaran';
$lang['ms_MY']['PaymentInformation.ss']['TABLESUMMARY'] = 'Kandungan pedati anda dipamerkan dalam borang ini bersama-sama rumusan semua yuran yang berkaitan dengan sesuatu pesanan dan ringkasan pilihan bayaran.';
$lang['ms_MY']['Product.ss']['ADD'] = 'Tambah &quot;%s&quot; ke dalam bakul';
$lang['ms_MY']['Product.ss']['ADDLINK'] = 'Tambah barangan ke dalam bakul';
$lang['ms_MY']['Product.ss']['ADDONE'] = 'Tambahkan satu &quot;%s&quot; ke dalam bakul';
$lang['ms_MY']['Product.ss']['AUTHOR'] = 'Penulis';
$lang['ms_MY']['Product.ss']['FEATURED'] = 'Ini adalah produk pilihan';
$lang['ms_MY']['Product.ss']['GOTOCHECKOUT'] = 'Teruskan ke checkout sekarang';
$lang['ms_MY']['Product.ss']['GOTOCHECKOUTLINK'] = '&raquo; Teruskan ke checkout';
$lang['ms_MY']['Product.ss']['IMAGE'] = 'Imej %s';
$lang['ms_MY']['Product.ss']['ItemID'] = 'Item #';
$lang['ms_MY']['Product.ss']['NOIMAGE'] = 'Maaf, tiada imej produk &quot;%s&quot;';
$lang['ms_MY']['Product.ss']['QUANTITYCART'] = 'Bilangan dalam bakul';
$lang['ms_MY']['Product.ss']['REMOVE'] = 'Keluarkan &quot;%s&quot; dari dalam bakul';
$lang['ms_MY']['Product.ss']['REMOVEALL'] = 'Keluarkan satu daripada &quot;%s&quot; dari dalam bakul';
$lang['ms_MY']['Product.ss']['REMOVELINK'] = '&raquo; telah dikeluarkan dari bakul';
$lang['ms_MY']['Product.ss']['SIZE'] = 'Saiz';
$lang['ms_MY']['ProductGroup.ss']['FEATURED'] = 'Produk-produk pilihan';
$lang['ms_MY']['ProductGroup.ss']['VIEWGROUP'] = 'Lihat kumpulan produk &quot;%s&quot;';
$lang['ms_MY']['ProductMenu.ss']['GOTOPAGE'] = 'Teruskan ke halaman %s';
$lang['ms_MY']['SSReport']['ALLCLICKHERE'] = 'Klik sini untuk memandang semua produk';
$lang['ms_MY']['SSReport']['INVOICE'] = 'Invois';
$lang['ms_MY']['SSReport']['PRINT'] = 'Cetak';
$lang['ms_MY']['SSReport']['VIEW'] = 'Pandang';

?>