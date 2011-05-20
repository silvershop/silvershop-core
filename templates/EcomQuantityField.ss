<div class="ecomquantityfield">
	<a class="addlink" href="$DecrementLink" title="<% sprintf(_t("REMOVEALL","Remove one of &quot;%s&quot; from your cart"),$Item.TableTitle) %>">
		<img src="$ThemeDir(ecommerce)/images/minus.gif" alt="-" />
	</a>	
	$Field
	<a class="removelink" href="$IncrementLink" title="<% sprintf(_t("ADDONE","Add one more of &quot;%s&quot; to your cart"),$Item.TableTitle) %>">
		<img src="$ThemeDir(ecommerce)/images/plus.gif" alt="+" />
	</a>
</div>