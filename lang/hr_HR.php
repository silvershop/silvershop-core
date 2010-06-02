<?php

/**
 * Croatian (Croatia) language pack
 * @package modules: ecommerce
 * @subpackage i18n
 */

i18n::include_locale_file('modules: ecommerce', 'en_US');

global $lang;

if(array_key_exists('hr_HR', $lang) && is_array($lang['hr_HR'])) {
	$lang['hr_HR'] = array_merge($lang['en_US'], $lang['hr_HR']);
} else {
	$lang['hr_HR'] = $lang['en_US'];
}

$lang['hr_HR']['AccountPage.ss']['COMPLETED'] = 'Realizirane narudžbe';
$lang['hr_HR']['AccountPage.ss']['HISTORY'] = 'Pregled vaših narudžbi';
$lang['hr_HR']['AccountPage.ss']['INCOMPLETE'] = 'Nepotpune narudžbe';
$lang['hr_HR']['AccountPage.ss']['NOCOMPLETED'] = 'Nisu pronađene realizirane narudžbe';
$lang['hr_HR']['AccountPage.ss']['NOINCOMPLETE'] = 'Nisu pronađenje nerealizirane narudžbe';
$lang['hr_HR']['AccountPage.ss']['ORDER'] = 'Narudžba #';
$lang['hr_HR']['AccountPage.ss']['READMORE'] = 'Pročitajte više o narudžbi #%s';
$lang['hr_HR']['Cart.ss']['HEADLINE'] = 'Moja košarica';
$lang['hr_HR']['Cart.ss']['NOITEMS'] = 'U vaša košarica je prazna';
$lang['hr_HR']['Cart.ss']['PRICE'] = 'Cijena';
$lang['hr_HR']['Cart.ss']['READMORE'] = 'Klikni ovdje za detalje o &quot;%s&quot;';
$lang['hr_HR']['Cart.ss']['Remove'] = 'Odstrani &quot;%s&quot; iz moje košarice';
$lang['hr_HR']['Cart.ss']['RemoveAlt'] = 'Odstrani';
$lang['hr_HR']['Cart.ss']['SHIPPING'] = 'Dostava';
$lang['hr_HR']['Cart.ss']['SUBTOTAL'] = 'Među suma';
$lang['hr_HR']['Cart.ss']['TOTAL'] = 'Ukupno';
$lang['hr_HR']['CheckoutPage.ss']['ORDERSTATUS'] = 'Status narudžbe';
$lang['hr_HR']['CheckoutPage.ss']['PROCESS'] = 'Proces';
$lang['hr_HR']['CheckoutPage_OrderIncomplete.ss']['INCOMPLETE'] = 'Nepotpuna narudžba';
$lang['hr_HR']['CheckoutPage_OrderIncomplete.ss']['ORDERSTATUS'] = 'Status narudžbe';
$lang['hr_HR']['CheckoutPage_OrderIncomplete.ss']['PROCESS'] = 'Proces';
$lang['hr_HR']['CheckoutPage_OrderSuccessful.ss']['ORDERSTATUS'] = 'Status narudžbe';
$lang['hr_HR']['CheckoutPage_OrderSuccessful.ss']['PROCESS'] = 'Proces';
$lang['hr_HR']['CheckoutPage_OrderSuccessful.ss']['SUCCESSFULl'] = 'Narudžba uspješna';
$lang['hr_HR']['ChequePayment']['MESSAGE'] = 'Prihvaćena uplata čekom. Primite na znanje: proizvod neće biti dostavljen sve dok se uplate ne uknjiži.';
$lang['hr_HR']['FindOrderReport']['DATERANGE'] = 'Raspon datuma';
$lang['hr_HR']['MemberForm']['DETAILSSAVED'] = 'Vaši detalji su snimljeni';
$lang['hr_HR']['MemberForm']['LOGGEDIN'] = 'Trenutno ste logirani kao';
$lang['hr_HR']['Order']['INCOMPLETE'] = 'Nepotpuna narudžba';
$lang['hr_HR']['Order']['SUCCESSFULL'] = 'Narudžba uspjela';
$lang['hr_HR']['OrderInformation.ss']['ADDRESS'] = 'Adresa';
$lang['hr_HR']['OrderInformation.ss']['BUYERSADDRESS'] = 'Adresa kupca';
$lang['hr_HR']['OrderInformation.ss']['CITY'] = 'Grad';
$lang['hr_HR']['OrderInformation.ss']['COUNTRY'] = 'Država';
$lang['hr_HR']['OrderInformation.ss']['CUSTOMERDETAILS'] = 'Detalji o kupcu';
$lang['hr_HR']['OrderInformation.ss']['DATE'] = 'Datum';
$lang['hr_HR']['OrderInformation.ss']['DETAILS'] = 'Datalji';
$lang['hr_HR']['OrderInformation.ss']['EMAIL'] = 'Email';
$lang['hr_HR']['OrderInformation.ss']['MOBILE'] = 'Mobitel';
$lang['hr_HR']['OrderInformation.ss']['NAME'] = 'Ime';
$lang['hr_HR']['OrderInformation.ss']['ORDERSUMMARY'] = 'Pregled narudžbe';
$lang['hr_HR']['OrderInformation.ss']['PAYMENTINFORMATION'] = 'Informazije o plaćanju';
$lang['hr_HR']['OrderInformation.ss']['PAYMENTMETHOD'] = 'Metoda';
$lang['hr_HR']['OrderInformation.ss']['PAYMENTSTATUS'] = 'Status plaćanja';
$lang['hr_HR']['OrderInformation.ss']['PHONE'] = 'Telefon';
$lang['hr_HR']['OrderInformation.ss']['PRICE'] = 'Cijena';
$lang['hr_HR']['OrderInformation.ss']['PRODUCT'] = 'Proizvod';
$lang['hr_HR']['OrderInformation.ss']['QUANTITY'] = 'Količina';
$lang['hr_HR']['OrderInformation.ss']['SHIPPING'] = 'Dostava';
$lang['hr_HR']['OrderInformation.ss']['SHIPPINGDETAILS'] = 'Detalji dostave';
$lang['hr_HR']['OrderInformation.ss']['SHIPPINGTO'] = 'Dostava na';
$lang['hr_HR']['OrderInformation.ss']['SUBTOTAL'] = 'Među suma';
$lang['hr_HR']['OrderInformation.ss']['TABLESUMMARY'] = 'Sadržaj vaše košarice je prikazan ovdje u formi sa svim troškovima vezanim uz narudžbu te detaljima plaćanja';
$lang['hr_HR']['OrderInformation.ss']['TOTALl'] = 'Ukupno';
$lang['hr_HR']['OrderInformation.ss']['TOTALPRICE'] = 'Ukupna cijena';
$lang['hr_HR']['OrderInformation_Editable.ss']['ADDONE'] = 'Dodaj jedan &quot;%s&quot; u košaricu';
$lang['hr_HR']['OrderInformation_Editable.ss']['NOITEMS'] = 'Vaša košarica je <strong>prazna</strong>';
$lang['hr_HR']['OrderInformation_Editable.ss']['ORDERINFORMATION'] = 'Informacije o narudžbi';
$lang['hr_HR']['OrderInformation_Editable.ss']['PRICE'] = 'Cijena';
$lang['hr_HR']['OrderInformation_Editable.ss']['PRODUCT'] = 'Proizvod';
$lang['hr_HR']['OrderInformation_Editable.ss']['QUANTITY'] = 'Količina';
$lang['hr_HR']['OrderInformation_Editable.ss']['READMORE'] = 'Kliknite ovdje za detalje o &quot;%s&quot;';
$lang['hr_HR']['OrderInformation_Editable.ss']['REMOVEONE'] = 'Makni iz košarice jedan &quot;%s&quot;';
$lang['hr_HR']['OrderInformation_Editable.ss']['SHIPPING'] = 'Dostava';
$lang['hr_HR']['OrderInformation_Editable.ss']['SHIPPINGTO'] = 'Dostava na';
$lang['hr_HR']['OrderInformation_Editable.ss']['SUBTOTAL'] = 'Među suma';
$lang['hr_HR']['OrderInformation_Editable.ss']['TABLESUMMARY'] = 'Sadržaj vaše košarice je prikazan ovdje u formi sa svim troškovima vezanim uz narudžbu te detaljima plaćanja';
$lang['hr_HR']['OrderInformation_Editable.ss']['TOTAL'] = 'Ukupno';
$lang['hr_HR']['OrderInformation_Editable.ss']['TOTALPRICE'] = 'Ukupna cijena';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['ADDRESS'] = 'Adresa';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['BUYERSADDRESS'] = 'Adresa kupca';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['CITY'] = 'Grad';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['COUNTRY'] = 'Država';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['CUSTOMERDETAILS'] = 'Detalji o kupcu';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['EMAIL'] = 'Email';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['MOBILE'] = 'Mobitel';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['NAME'] = 'Ime';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['ORDERINFO'] = 'Informazije o narudđbi #';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['PHONE'] = 'Telefon';
$lang['hr_HR']['OrderInformation_NoPricing.ss']['TABLESUMMARY'] = 'Sadržaj vaše košarice je prikazan ovdje u formi sa svim troškovima vezanim uz narudžbu te detaljima plaćanja';
$lang['hr_HR']['OrderInformation_PackingSlip.ss']['DESCRIPTION'] = 'Opis';
$lang['hr_HR']['OrderInformation_PackingSlip.ss']['ORDERDATE'] = 'Datum narudžbe';
$lang['hr_HR']['OrderInformation_PackingSlip.ss']['ORDERNUMBER'] = 'Broj narudžbe';
$lang['hr_HR']['OrderInformation_PackingSlip.ss']['QUANTITY'] = 'Količina';
$lang['hr_HR']['OrderInformation_Print.ss']['PAGETITLE'] = 'Ispiši narudžbe';
$lang['hr_HR']['OrderReport']['CHANGESTATUS'] = 'Promijeni status narudžbe';
$lang['hr_HR']['OrderReport']['NOTEEMAIL'] = 'Poruka/Email';
$lang['hr_HR']['OrderReport']['PRINTEACHORDER'] = 'Ispiši sve prikazane narudžbe';
$lang['hr_HR']['OrderReport']['SENDNOTETO'] = 'Pošalji poruku %s (%s)';
$lang['hr_HR']['PaymentInformation.ss']['DATE'] = 'Datum';
$lang['hr_HR']['PaymentInformation.ss']['DETAILS'] = 'Detalji';
$lang['hr_HR']['PaymentInformation.ss']['PAYMENTINFORMATION'] = 'Informacije o plačanju';
$lang['hr_HR']['PaymentInformation.ss']['PAYMENTMETHOD'] = 'Metoda';
$lang['hr_HR']['PaymentInformation.ss']['PAYMENTSTATUS'] = 'Status plačanja';
$lang['hr_HR']['Product.ss']['AUTHOR'] = 'Autor';
$lang['hr_HR']['Product.ss']['IMAGE'] = 'Slika %s';
$lang['hr_HR']['Product.ss']['NOIMAGE'] = 'Nažalost nema slike &quot;%s&quot;';
$lang['hr_HR']['Product.ss']['QUANTITYCART'] = 'Količina u košarici';
$lang['hr_HR']['Product.ss']['SIZE'] = 'Veličina';
$lang['hr_HR']['ProductGroupItem.ss']['ADD'] = 'Dodaj &quot;%s&quot; u vašu košaricu';
$lang['hr_HR']['ProductGroupItem.ss']['ADDLINK'] = 'Dodaj u košaricu';
$lang['hr_HR']['ProductGroupItem.ss']['ADDONE'] = 'Dodaj još jedan  &quot;%s&quot; u košaricu';
$lang['hr_HR']['ProductGroupItem.ss']['AUTHOR'] = 'Autor';
$lang['hr_HR']['ProductGroupItem.ss']['IMAGE'] = 'Slika %s';
$lang['hr_HR']['ProductGroupItem.ss']['NOIMAGE'] = 'Nažalost nema slike za &quot;%s&quot;';
$lang['hr_HR']['ProductGroupItem.ss']['QUANTITY'] = 'Količina';
$lang['hr_HR']['ProductGroupItem.ss']['QUANTITYCART'] = 'Količina u košarici';
$lang['hr_HR']['ProductGroupItem.ss']['READMORE'] = 'Klikni ovdje za detalje o &quot;%s&quot;';
$lang['hr_HR']['ProductGroupItem.ss']['REMOVE'] = 'Odstrani  &quot;%s&quot;  iz košarice';
$lang['hr_HR']['ProductGroupItem.ss']['REMOVELINK'] = '&raquo; Odstrani iz košarice';
$lang['hr_HR']['ProductMenu.ss']['GOTOPAGE'] = 'Idi na %s stranicu';
$lang['hr_HR']['SSReport']['ALLCLICKHERE'] = 'Klikni ovdje za pregled svih proizvoda';
$lang['hr_HR']['SSReport']['PRINT'] = 'Ispiši';
$lang['hr_HR']['ViewAllProducts.ss']['AUTHOR'] = 'Autor';
$lang['hr_HR']['ViewAllProducts.ss']['CATEGORIES'] = 'Kategorije';
$lang['hr_HR']['ViewAllProducts.ss']['IMAGE'] = 'Slika %s';
$lang['hr_HR']['ViewAllProducts.ss']['LASTEDIT'] = 'Ažurirano';
$lang['hr_HR']['ViewAllProducts.ss']['LINK'] = 'Link';
$lang['hr_HR']['ViewAllProducts.ss']['NOCONTENT'] = 'Nije postavljen sadržaj.';
$lang['hr_HR']['ViewAllProducts.ss']['NOIMAGE'] = 'Nažalst nema slike &quot;%s&quot;';
$lang['hr_HR']['ViewAllProducts.ss']['PRICE'] = 'Cijena';
$lang['hr_HR']['ViewAllProducts.ss']['PRODUCTID'] = 'ID proizvoda';
$lang['hr_HR']['ViewAllProducts.ss']['WEIGHT'] = 'Težina';

?>