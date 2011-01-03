<div class="ecomquantityfield">
	<a class="removeOneLink" href="$DecrementLink" title="<% sprintf(_t("REMOVEONE","Remove one of &quot;%s&quot; from your cart"),$OrderItem.TableTitle) %>">
		<img src="$ThemeDir(ecommerce)/images/minus.gif" alt="-" />
	</a>
	$Field
	<a class="addOneLink" href="$IncrementLink" title="<% sprintf(_t("ADDONE","Add one more of &quot;%s&quot; to your cart"),$OrderItem.TableTitle) %>">
		<img src="$ThemeDir(ecommerce)/images/plus.gif" alt="+" />
	</a>
	$AJAXLinkHiddenField
</div>
