<?php

/**
 * Finnish (Finland) language pack
 * @package modules: ecommerce
 * @subpackage i18n
 */

i18n::include_locale_file('modules: ecommerce', 'en_US');

global $lang;

if(array_key_exists('fi_FI', $lang) && is_array($lang['fi_FI'])) {
	$lang['fi_FI'] = array_merge($lang['en_US'], $lang['fi_FI']);
} else {
	$lang['fi_FI'] = $lang['en_US'];
}

$lang['fi_FI']['AccountPage.ss']['COMPLETED'] = 'Suoritetut tilaukset';
$lang['fi_FI']['AccountPage.ss']['HISTORY'] = 'Sinun Tilaushistoriasi';
$lang['fi_FI']['AccountPage.ss']['INCOMPLETE'] = 'Suorittamattomat tilaukset';
$lang['fi_FI']['AccountPage.ss']['NOCOMPLETED'] = 'Suoritettuja tilauksia ei löytynyt.';
$lang['fi_FI']['AccountPage.ss']['NOINCOMPLETE'] = 'Suorittamattomia tilauksia ei löytynyt';
$lang['fi_FI']['AccountPage.ss']['ORDER'] = 'Tilaus #';
$lang['fi_FI']['AccountPage.ss']['READMORE'] = 'Lue lisää tilauksesta #%s';
$lang['fi_FI']['AccountPage_order.ss']['ADDRESS'] = 'Katuosoite';
$lang['fi_FI']['AccountPage_order.ss']['CITY'] = 'Kaupunki';
$lang['fi_FI']['AccountPage_order.ss']['COUNTRY'] = 'Valtio';
$lang['fi_FI']['AccountPage_order.ss']['DATE'] = 'Päiväys';
$lang['fi_FI']['AccountPage_order.ss']['NAME'] = 'Nimi';
$lang['fi_FI']['Cart.ss']['CheckoutClick'] = 'Paina tästä mennäksi kassalle';
$lang['fi_FI']['Cart.ss']['CheckoutGoTo'] = 'Mene kassalle';
$lang['fi_FI']['Cart.ss']['HEADLINE'] = 'Ostoskärry';
$lang['fi_FI']['Cart.ss']['NOITEMS'] = 'Ostoskärryssäsi ei ole tuotteita';
$lang['fi_FI']['Cart.ss']['PRICE'] = 'Hinta';
$lang['fi_FI']['Cart.ss']['READMORE'] = 'Paina tästä lukeaksi lisää tuotteesta &quot;%s&quot;';
$lang['fi_FI']['Cart.ss']['Remove'] = 'Poista &quot;%s&quote; ostoskärrystäsi';
$lang['fi_FI']['Cart.ss']['RemoveAlt'] = 'Poista';
$lang['fi_FI']['Cart.ss']['SHIPPING'] = 'Kuljetus';
$lang['fi_FI']['Cart.ss']['SUBTOTAL'] = 'Yhteensä';
$lang['fi_FI']['Cart.ss']['TOTAL'] = 'Yhteensä';
$lang['fi_FI']['CheckoutPage']['NOPAGE'] = 'Tällä sivustolla ei ole KassaSivua - ole hyvä ja luo sellainen!';
$lang['fi_FI']['CheckoutPage.ss']['CHECKOUT'] = 'Kassa';
$lang['fi_FI']['CheckoutPage.ss']['ORDERSTATUS'] = 'Tilauksen tila';
$lang['fi_FI']['CheckoutPage_OrderIncomplete.ss']['BACKTOCHECKOUT'] = 'Paina tästä palataksesi Kassalle';
$lang['fi_FI']['CheckoutPage_OrderIncomplete.ss']['CHECKOUT'] = 'Kassa';
$lang['fi_FI']['CheckoutPage_OrderIncomplete.ss']['CHEQUEINSTRUCTIONS'] = 'Saat sähköpostiisi ohjeet jos maksoit shekillä.';
$lang['fi_FI']['CheckoutPage_OrderIncomplete.ss']['DETAILSSUBMITTED'] = 'Tässä ovat lähettämäsi tiedot';
$lang['fi_FI']['CheckoutPage_OrderIncomplete.ss']['INCOMPLETE'] = 'Tilausta ei ole suoritettu';
$lang['fi_FI']['CheckoutPage_OrderIncomplete.ss']['ORDERSTATUS'] = 'Tilauksen tila';
$lang['fi_FI']['CheckoutPage_OrderSuccessful.ss']['BACKTOCHECKOUT'] = 'Klikkaa tästä palataksesi kassalle';
$lang['fi_FI']['CheckoutPage_OrderSuccessful.ss']['CHECKOUT'] = 'Kassa';
$lang['fi_FI']['CheckoutPage_OrderSuccessful.ss']['ORDERSTATUS'] = 'Tilauksen tila';
$lang['fi_FI']['CheckoutPage_OrderSuccessful.ss']['SUCCESSFULl'] = 'Tilaus onnistui';
$lang['fi_FI']['ChequePayment']['MESSAGE'] = 'Maksu hyväksytty shekin välityksellä. Huomaa: Tuotteita ei lähetetä ennen maksun saapumista.';
$lang['fi_FI']['FindOrderReport']['DATERANGE'] = 'Päivämäärä-alue';
$lang['fi_FI']['MemberForm']['DETAILSSAVED'] = 'Tietosi on tallennettu';
$lang['fi_FI']['MemberForm']['LOGGEDIN'] = 'Olet kirjautuneena sisään.';
$lang['fi_FI']['OrderInformation.ss']['ADDRESS'] = 'Osoite';
$lang['fi_FI']['OrderInformation.ss']['AMOUNT'] = 'Määrä';
$lang['fi_FI']['OrderInformation.ss']['BUYERSADDRESS'] = 'Maksajan osoite';
$lang['fi_FI']['OrderInformation.ss']['CITY'] = 'Kaupunki';
$lang['fi_FI']['OrderInformation.ss']['COUNTRY'] = 'Maa';
$lang['fi_FI']['OrderInformation.ss']['CUSTOMERDETAILS'] = 'Asiakkaan tiedot';
$lang['fi_FI']['OrderInformation.ss']['DATE'] = 'Päivämäärä';
$lang['fi_FI']['OrderInformation.ss']['DETAILS'] = 'Yksityiskohdat';
$lang['fi_FI']['OrderInformation.ss']['EMAIL'] = 'Sähköposti';
$lang['fi_FI']['OrderInformation.ss']['MOBILE'] = 'Matkapuhelin';
$lang['fi_FI']['OrderInformation.ss']['NAME'] = 'Nimi';
$lang['fi_FI']['OrderInformation.ss']['ORDERSUMMARY'] = 'Tilauksen yhteenveto';
$lang['fi_FI']['OrderInformation.ss']['PAYMENTID'] = 'Maksun tunniste';
$lang['fi_FI']['OrderInformation.ss']['PAYMENTINFORMATION'] = 'Maksutiedot';
$lang['fi_FI']['OrderInformation.ss']['PAYMENTMETHOD'] = 'Menetelmä';
$lang['fi_FI']['OrderInformation.ss']['PAYMENTSTATUS'] = 'Maksun tila';
$lang['fi_FI']['OrderInformation.ss']['PHONE'] = 'Puhelin';
$lang['fi_FI']['OrderInformation.ss']['PRICE'] = 'Hinta';
$lang['fi_FI']['OrderInformation.ss']['PRODUCT'] = 'Tuote';
$lang['fi_FI']['OrderInformation.ss']['QUANTITY'] = 'Määrä';
$lang['fi_FI']['OrderInformation.ss']['SHIPPING'] = 'Kuljetus';
$lang['fi_FI']['OrderInformation.ss']['SHIPPINGTO'] = 'vastaanottaja';
$lang['fi_FI']['OrderInformation.ss']['SUBTOTAL'] = 'Yhteensä';
$lang['fi_FI']['OrderInformation.ss']['TOTALl'] = 'Yhteensä';
$lang['fi_FI']['OrderInformation.ss']['TOTALOUTSTANDING'] = 'Maksamatonta yhteensä';
$lang['fi_FI']['OrderInformation.ss']['TOTALPRICE'] = 'Yhteishinta';
$lang['fi_FI']['OrderInformation_Editable.ss']['ADDONE'] = 'Lisää yksi &quot;%s&quot; ostoskärryysi';
$lang['fi_FI']['OrderInformation_Editable.ss']['NOITEMS'] = 'Osotoskärryssäsi <strong>ei</strong> ole tuotteita';
$lang['fi_FI']['OrderInformation_Editable.ss']['ORDERINFORMATION'] = 'Tilaustiedot';
$lang['fi_FI']['OrderInformation_Editable.ss']['PRICE'] = 'Hinta';
$lang['fi_FI']['OrderInformation_Editable.ss']['PRODUCT'] = 'Tuote';
$lang['fi_FI']['OrderInformation_Editable.ss']['QUANTITY'] = 'Määrä';
$lang['fi_FI']['OrderInformation_Editable.ss']['READMORE'] = 'Paina tästä lukeaksesi lisää tuotteesta &quot;%s&quot;';
$lang['fi_FI']['OrderInformation_Editable.ss']['REMOVEONE'] = 'Poista yksi %quot;%s&quot; ostoskärrystäsi';
$lang['fi_FI']['OrderInformation_Editable.ss']['SHIPPING'] = 'Kuljetus';
$lang['fi_FI']['OrderInformation_Editable.ss']['SHIPPINGTO'] = 'vastaanottaja';
$lang['fi_FI']['OrderInformation_Editable.ss']['SUBTOTAL'] = 'Yhteensä';
$lang['fi_FI']['OrderInformation_Editable.ss']['TOTAL'] = 'Yhteensä';
$lang['fi_FI']['OrderInformation_Editable.ss']['TOTALPRICE'] = 'Hinta yhteensä';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['ADDRESS'] = 'Osoite';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['BUYERSADDRESS'] = 'Ostajan Osoite';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['CITY'] = 'Kaupunki';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['COUNTRY'] = 'Maa';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['CUSTOMERDETAILS'] = 'Asiakkaan Tiedot';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['EMAIL'] = 'Sähköposti';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['MOBILE'] = 'Matkapuhelin';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['NAME'] = 'Nimi';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['ORDERINFO'] = 'Tietoja Tilauksesta';
$lang['fi_FI']['OrderInformation_NoPricing.ss']['PHONE'] = 'Puhelin';
$lang['fi_FI']['OrderInformation_PackingSlip.ss']['DESCRIPTION'] = 'Kuvaus';
$lang['fi_FI']['OrderInformation_PackingSlip.ss']['ITEM'] = 'Tuote';
$lang['fi_FI']['OrderInformation_PackingSlip.ss']['ORDERDATE'] = 'Tilauksen päivämäärä';
$lang['fi_FI']['OrderInformation_PackingSlip.ss']['ORDERNUMBER'] = 'Tilausnumero';
$lang['fi_FI']['OrderInformation_PackingSlip.ss']['QUANTITY'] = 'Määrä';
$lang['fi_FI']['OrderInformation_Print.ss']['PAGETITLE'] = 'Tulosta tilaukset';
$lang['fi_FI']['OrderReport']['CHANGESTATUS'] = 'Vaihda tilauksen tilaa';
$lang['fi_FI']['OrderReport']['NOTEEMAIL'] = 'Muistio/Sähköposti';
$lang['fi_FI']['OrderReport']['PRINTEACHORDER'] = 'Tulosta kaikki näytetyt tilaukset';
$lang['fi_FI']['Order_statusEmail.ss']['STATUSCHANGE'] = 'Tila vaihdettu "%s" Tilauksessa #';
$lang['fi_FI']['PaymentInformation.ss']['AMOUNT'] = 'Määrä';
$lang['fi_FI']['PaymentInformation.ss']['DATE'] = 'Päivämäärä';
$lang['fi_FI']['PaymentInformation.ss']['DETAILS'] = 'Yksityiskohdat';
$lang['fi_FI']['PaymentInformation.ss']['PAYMENTID'] = 'Maksun tunnus';
$lang['fi_FI']['PaymentInformation.ss']['PAYMENTINFORMATION'] = 'Maksun tiedot';
$lang['fi_FI']['PaymentInformation.ss']['PAYMENTMETHOD'] = 'Menetelmä';
$lang['fi_FI']['PaymentInformation.ss']['PAYMENTSTATUS'] = 'Maksun tila';
$lang['fi_FI']['Product.ss']['ADD'] = 'Lisää &quot;%s&quot; ostoskärryysi';
$lang['fi_FI']['Product.ss']['ADDLINK'] = 'Lisää tämä tuote ostoskärryyn';
$lang['fi_FI']['Product.ss']['ADDONE'] = 'Lisää yksi &quot;%s&quot; ostoskärryysi';
$lang['fi_FI']['Product.ss']['AUTHOR'] = 'Kirjoittaja';
$lang['fi_FI']['Product.ss']['FEATURED'] = 'Tämä tuote on tarjouksessa.';
$lang['fi_FI']['Product.ss']['GOTOCHECKOUT'] = 'Mene kassalle nyt';
$lang['fi_FI']['Product.ss']['GOTOCHECKOUTLINK'] = '&raquo; Mene kassalle';
$lang['fi_FI']['Product.ss']['IMAGE'] = '%s kuva';
$lang['fi_FI']['Product.ss']['ItemID'] = 'Tuote #';
$lang['fi_FI']['Product.ss']['NOIMAGE'] = 'Valitettavasti tuotteelle &quot;%s%quot; ei ole tuotekuvaa';
$lang['fi_FI']['Product.ss']['QUANTITYCART'] = 'Ostoskärryssä';
$lang['fi_FI']['Product.ss']['REMOVE'] = 'Poista &quot;%s&quot; ostoskärrystäsi';
$lang['fi_FI']['Product.ss']['REMOVEALL'] = 'Poista yksi &quot;%s&quot; ostoskärrystäsi';
$lang['fi_FI']['Product.ss']['REMOVELINK'] = '&raquo; Poista ostoskärrystäsi';
$lang['fi_FI']['Product.ss']['SIZE'] = 'Koko';
$lang['fi_FI']['ProductGroup.ss']['FEATURED'] = 'Tarjouksessa olevat tuotteet';
$lang['fi_FI']['ProductGroupItem.ss']['ADD'] = 'Lisää &quot;%s&quot; ostoskärryysi';
$lang['fi_FI']['ProductGroupItem.ss']['ADDLINK'] = 'Lisää tämä tuote ostoskärryysi';
$lang['fi_FI']['ProductGroupItem.ss']['ADDONE'] = 'Lisää yksi &quot;%s&quot; ostoskärryysi';
$lang['fi_FI']['ProductGroupItem.ss']['AUTHOR'] = 'Kirjoittaja';
$lang['fi_FI']['ProductGroupItem.ss']['IMAGE'] = '%s kuva';
$lang['fi_FI']['ProductGroupItem.ss']['NOIMAGE'] = 'Valitettavasti tuotteelle &quot;%s&quot; ei ole tuotekuvaa';
$lang['fi_FI']['ProductGroupItem.ss']['QUANTITY'] = 'Määrä';
$lang['fi_FI']['ProductGroupItem.ss']['READMORE'] = 'Paina tästä lukeaksesi lisää tuotteesta &quot;%s&quot;';
$lang['fi_FI']['ProductGroupItem.ss']['READMORECONTENT'] = 'Paina tästä lukeaksesi lisää &raquo;';
$lang['fi_FI']['ProductGroupItem.ss']['REMOVE'] = 'Poista &quot;%s&quot; ostoskärrystäsi';
$lang['fi_FI']['ProductGroupItem.ss']['REMOVELINK'] = '&raquo; Poista ostoskärrystä';
$lang['fi_FI']['ProductGroupItem.ss']['REMOVEONE'] = 'Poista yksi &quot;%s&quot; ostoskärrystäsi';
$lang['fi_FI']['ProductMenu.ss']['GOTOPAGE'] = 'Mene %s sivulle';
$lang['fi_FI']['SSReport']['ALLCLICKHERE'] = 'Paina tästä nähdäksesi kaikki tuotteet';
$lang['fi_FI']['SSReport']['INVOICE'] = 'laskuta';
$lang['fi_FI']['SSReport']['PRINT'] = 'tulosta';
$lang['fi_FI']['SSReport']['VIEW'] = 'katso';
$lang['fi_FI']['ViewAllProducts.ss']['AUTHOR'] = 'Kirjoittaja';
$lang['fi_FI']['ViewAllProducts.ss']['CATEGORIES'] = 'Luokat';
$lang['fi_FI']['ViewAllProducts.ss']['IMAGE'] = '&s kuva';
$lang['fi_FI']['ViewAllProducts.ss']['LASTEDIT'] = 'Viimeeksi muokattu';
$lang['fi_FI']['ViewAllProducts.ss']['LINK'] = 'Linkki';
$lang['fi_FI']['ViewAllProducts.ss']['NOCONTENT'] = 'Sisältöä ei ole asetettu';
$lang['fi_FI']['ViewAllProducts.ss']['NOIMAGE'] = 'Valitettavasti &quot;%s&quot;:lle ei ole kuvaa';
$lang['fi_FI']['ViewAllProducts.ss']['PRICE'] = 'Hinta';
$lang['fi_FI']['ViewAllProducts.ss']['PRODUCTID'] = 'Tuotetunnus';
$lang['fi_FI']['ViewAllProducts.ss']['WEIGHT'] = 'Paino';

?>