<%-- Shared closing shell for every shop notification email. Renders a simple footer line (the shop name)
     and closes the content card and document opened by SilverShop\Includes\OrderEmailHeader. Override this
     template to restyle the footer of every shop email at once. --%>
                        <tr>
                            <td class="silvershop-email__footer" colspan="2">$SiteConfig.Title</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
