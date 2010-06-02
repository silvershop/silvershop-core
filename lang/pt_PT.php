<?php

/**
 * Portuguese (Portugal) language pack
 * @package modules: ecommerce
 * @subpackage i18n
 */

i18n::include_locale_file('modules: ecommerce', 'en_US');

global $lang;

if(array_key_exists('pt_PT', $lang) && is_array($lang['pt_PT'])) {
	$lang['pt_PT'] = array_merge($lang['en_US'], $lang['pt_PT']);
} else {
	$lang['pt_PT'] = $lang['en_US'];
}

$lang['pt_PT']['AccountPage.ss']['HISTORY'] = 'Histórico de pedidos';
$lang['pt_PT']['Cart.ss']['CheckoutClick'] = 'Carregue aqui para ir para a caixa';
$lang['pt_PT']['Cart.ss']['CheckoutGoTo'] = 'Ir para a caixa';
$lang['pt_PT']['Cart.ss']['HEADLINE'] = 'Carrinho de compras';
$lang['pt_PT']['Cart.ss']['NOITEMS'] = 'Não existem produtos no seu carrinho';
$lang['pt_PT']['Cart.ss']['PRICE'] = 'Preço';
$lang['pt_PT']['Cart.ss']['READMORE'] = 'Clique aqui para ler mais sobre &quot;%s&quot;';
$lang['pt_PT']['Cart.ss']['Remove'] = 'Remover &quot;%s&quot; do carrinho de compras';
$lang['pt_PT']['Cart.ss']['RemoveAlt'] = 'Remover';
$lang['pt_PT']['Cart.ss']['SHIPPING'] = 'Envio';
$lang['pt_PT']['Cart.ss']['SUBTOTAL'] = 'Subtotal';
$lang['pt_PT']['Cart.ss']['TOTAL'] = 'Total';
$lang['pt_PT']['CheckoutPage.ss']['PROCESS'] = 'Processar';
$lang['pt_PT']['CheckoutPage_OrderIncomplete.ss']['DETAILSSUBMITTED'] = 'Aqui estão os detalhes enviados';
$lang['pt_PT']['CheckoutPage_OrderIncomplete.ss']['PROCESS'] = 'Processar';
$lang['pt_PT']['CheckoutPage_OrderSuccessful.ss']['PROCESS'] = 'Processar';
$lang['pt_PT']['ChequePayment']['MESSAGE'] = 'Pagamento aceite via Cheque. Por favor note: os produtos não serão enviados até que o pagamento tenha sido recebido';
$lang['pt_PT']['FindOrderReport']['DATERANGE'] = 'Entre datas';
$lang['pt_PT']['MemberForm']['DETAILSSAVED'] = 'Os seus detalhes foram guardados';
$lang['pt_PT']['MemberForm']['LOGGEDIN'] = 'Está autenticado como';
$lang['pt_PT']['OrderInformation.ss']['ADDRESS'] = 'Morada';
$lang['pt_PT']['OrderInformation.ss']['AMOUNT'] = 'Quantia';
$lang['pt_PT']['OrderInformation.ss']['BUYERSADDRESS'] = 'Morada do Comprador';
$lang['pt_PT']['OrderInformation.ss']['CITY'] = 'Localidade';
$lang['pt_PT']['OrderInformation.ss']['COUNTRY'] = 'País';
$lang['pt_PT']['OrderInformation.ss']['CUSTOMERDETAILS'] = 'Detalhes do Cliente';
$lang['pt_PT']['OrderInformation.ss']['DATE'] = 'Data';
$lang['pt_PT']['OrderInformation.ss']['DETAILS'] = 'Detalhes';
$lang['pt_PT']['OrderInformation.ss']['EMAIL'] = 'Email';
$lang['pt_PT']['OrderInformation.ss']['MOBILE'] = 'Telemóvel';
$lang['pt_PT']['OrderInformation.ss']['NAME'] = 'Nome';
$lang['pt_PT']['OrderInformation.ss']['ORDERSUMMARY'] = 'Sumário do Pedido';
$lang['pt_PT']['OrderInformation.ss']['PAYMENTID'] = 'ID do pagamento';
$lang['pt_PT']['OrderInformation.ss']['PAYMENTINFORMATION'] = 'Informação do pagamento';
$lang['pt_PT']['OrderInformation.ss']['PAYMENTMETHOD'] = 'Método';
$lang['pt_PT']['OrderInformation.ss']['PAYMENTSTATUS'] = 'Estado do pagamento';
$lang['pt_PT']['OrderInformation.ss']['PHONE'] = 'Telefone';
$lang['pt_PT']['OrderInformation.ss']['PRICE'] = 'Preço';
$lang['pt_PT']['OrderInformation.ss']['PRODUCT'] = 'Produto';
$lang['pt_PT']['OrderInformation.ss']['QUANTITY'] = 'Quantidade';
$lang['pt_PT']['OrderInformation.ss']['READMORE'] = 'Clique aqui para ler mais sobre &quot;%s&quot;';
$lang['pt_PT']['OrderInformation.ss']['SHIPPING'] = 'Envio';
$lang['pt_PT']['OrderInformation.ss']['SHIPPINGTO'] = 'para';
$lang['pt_PT']['OrderInformation.ss']['SUBTOTAL'] = 'Sub-total';
$lang['pt_PT']['OrderInformation.ss']['TOTAL'] = 'Total';
$lang['pt_PT']['OrderInformation.ss']['TOTALl'] = 'Total';
$lang['pt_PT']['OrderInformation.ss']['TOTALOUTSTANDING'] = 'Valor Total ';
$lang['pt_PT']['OrderInformation.ss']['TOTALPRICE'] = 'Preço Total';
$lang['pt_PT']['OrderInformation_Editable.ss']['NOITEMS'] = 'Não existem produtos no seu carrinho';
$lang['pt_PT']['OrderInformation_Editable.ss']['ORDERINFORMATION'] = 'Informação do pedido';
$lang['pt_PT']['OrderInformation_Editable.ss']['PRICE'] = 'Preço';
$lang['pt_PT']['OrderInformation_Editable.ss']['PRODUCT'] = 'Produto';
$lang['pt_PT']['OrderInformation_Editable.ss']['QUANTITY'] = 'Quantidade';
$lang['pt_PT']['OrderInformation_Editable.ss']['READMORE'] = 'Clique aqui para ler mais sobre &quot;%s&quot;';
$lang['pt_PT']['OrderInformation_Editable.ss']['SHIPPING'] = 'Envio';
$lang['pt_PT']['OrderInformation_Editable.ss']['SHIPPINGTO'] = 'para';
$lang['pt_PT']['OrderInformation_Editable.ss']['SUBTOTAL'] = 'Sub-total';
$lang['pt_PT']['OrderInformation_Editable.ss']['TOTAL'] = 'Total';
$lang['pt_PT']['OrderInformation_Editable.ss']['TOTALPRICE'] = 'Preço Total';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['ADDRESS'] = 'Morada';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['BUYERSADDRESS'] = 'Morada do Comprador';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['CITY'] = 'Localidade';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['COUNTRY'] = 'País';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['CUSTOMERDETAILS'] = 'Detalhes do Cliente';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['EMAIL'] = 'Email';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['MOBILE'] = 'Telemóvel';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['NAME'] = 'Nome';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['ORDERINFO'] = 'Informação do Pedido #';
$lang['pt_PT']['OrderInformation_NoPricing.ss']['PHONE'] = 'Telefone';
$lang['pt_PT']['OrderInformation_PackingSlip.ss']['DESCRIPTION'] = 'Descrição';
$lang['pt_PT']['OrderInformation_PackingSlip.ss']['ITEM'] = 'Produto';
$lang['pt_PT']['OrderInformation_PackingSlip.ss']['ORDERDATE'] = 'Data do pedido';
$lang['pt_PT']['OrderInformation_PackingSlip.ss']['ORDERNUMBER'] = 'Número do pedido';
$lang['pt_PT']['OrderInformation_PackingSlip.ss']['QUANTITY'] = 'Quantidade';
$lang['pt_PT']['OrderInformation_Print.ss']['PAGETITLE'] = 'Imprimir Encomendas';
$lang['pt_PT']['OrderReport']['CHANGESTATUS'] = 'Mudar o estado da ordem';
$lang['pt_PT']['OrderReport']['NOTEEMAIL'] = 'Nota/Email';
$lang['pt_PT']['OrderReport']['PRINTEACHORDER'] = 'Imprimir todas as ordens mostradas';
$lang['pt_PT']['OrderReport']['SENDNOTETO'] = 'Enviar esta nota para %s(%s)';
$lang['pt_PT']['PaymentInformation.ss']['AMOUNT'] = 'Quantia';
$lang['pt_PT']['PaymentInformation.ss']['DATE'] = 'Data';
$lang['pt_PT']['PaymentInformation.ss']['DETAILS'] = 'Detalhes';
$lang['pt_PT']['PaymentInformation.ss']['PAYMENTID'] = 'ID do pagamento';
$lang['pt_PT']['PaymentInformation.ss']['PAYMENTINFORMATION'] = 'Informação do pagamento';
$lang['pt_PT']['PaymentInformation.ss']['PAYMENTMETHOD'] = 'Método';
$lang['pt_PT']['PaymentInformation.ss']['PAYMENTSTATUS'] = 'Estado do Pagamento';
$lang['pt_PT']['Product.ss']['ADD'] = 'Adicionar &quot;%s&quot; para o carrinho';
$lang['pt_PT']['Product.ss']['ADDLINK'] = 'Adicionar este produto para o carrinho';
$lang['pt_PT']['Product.ss']['AUTHOR'] = 'Autor';
$lang['pt_PT']['Product.ss']['IMAGE'] = 'imagem %s ';
$lang['pt_PT']['Product.ss']['ItemID'] = 'Produto #';
$lang['pt_PT']['Product.ss']['NOIMAGE'] = 'Imagem do produto &quot;%s&quot; não disponível ';
$lang['pt_PT']['Product.ss']['QUANTITYCART'] = 'Quantidade no carrinho';
$lang['pt_PT']['Product.ss']['REMOVE'] = 'Remover &quot;%s&quot; do seu carrinho';
$lang['pt_PT']['Product.ss']['REMOVELINK'] = '&raquo; Remover do carrinho';
$lang['pt_PT']['ProductGroup.ss']['VIEWGROUP'] = 'Ver o grupo do produto &quot;%s&quot;';
$lang['pt_PT']['ProductGroupItem.ss']['ADD'] = 'Adicionar &quot;%s&quot; para o seu carrinho';
$lang['pt_PT']['ProductGroupItem.ss']['ADDLINK'] = 'Adicionar este produto para o carrinho';
$lang['pt_PT']['ProductGroupItem.ss']['AUTHOR'] = 'Autor';
$lang['pt_PT']['ProductGroupItem.ss']['IMAGE'] = '%s imagem';
$lang['pt_PT']['ProductGroupItem.ss']['NOIMAGE'] = 'Imagem não disponível para &quot;%s&quot;';
$lang['pt_PT']['ProductGroupItem.ss']['QUANTITY'] = 'Quantidade';
$lang['pt_PT']['ProductGroupItem.ss']['READMORE'] = 'Clique aqui para ler mais sobre &quot;%s&quot;';
$lang['pt_PT']['ProductGroupItem.ss']['REMOVE'] = 'Remover &quot;%s&quot; do seu carrinho';
$lang['pt_PT']['ProductGroupItem.ss']['REMOVELINK'] = '&raquo; Remover do carrinho';
$lang['pt_PT']['ProductMenu.ss']['GOTOPAGE'] = 'Ir para a página %s';
$lang['pt_PT']['SSReport']['ALLCLICKHERE'] = 'Clique aqui para ver todos os produtos';
$lang['pt_PT']['SSReport']['INVOICE'] = 'Factura';
$lang['pt_PT']['SSReport']['PRINT'] = 'Imprimir';
$lang['pt_PT']['SSReport']['VIEW'] = 'Ver';
$lang['pt_PT']['ViewAllProducts.ss']['AUTHOR'] = 'Autor';
$lang['pt_PT']['ViewAllProducts.ss']['CATEGORIES'] = 'Categorias';
$lang['pt_PT']['ViewAllProducts.ss']['IMAGE'] = 'Imagem %s';
$lang['pt_PT']['ViewAllProducts.ss']['LASTEDIT'] = 'Editado pela última vez';
$lang['pt_PT']['ViewAllProducts.ss']['LINK'] = 'Link';
$lang['pt_PT']['ViewAllProducts.ss']['NOCONTENT'] = 'Conteúdo não adicionado';
$lang['pt_PT']['ViewAllProducts.ss']['NOIMAGE'] = 'Imagem não inserida para &quot;%s&quot;';
$lang['pt_PT']['ViewAllProducts.ss']['NOSUBJECTS'] = 'Sem assunto definido';
$lang['pt_PT']['ViewAllProducts.ss']['PRICE'] = 'Preço';
$lang['pt_PT']['ViewAllProducts.ss']['PRODUCTID'] = 'ID do Produto';
$lang['pt_PT']['ViewAllProducts.ss']['WEIGHT'] = 'Peso';

?>