<style type="text/css">
    /* Flat, self-contained transactional-email styling: a white card on a light canvas, system sans-serif,
       black accents, horizontal rules only. Kept table-based with an inline <style> for broad email-client
       support (no flexbox/grid/custom-properties — mail clients strip those). Styles the email wrapper
       (.silvershop-email*) and the shared order-summary classes (.silvershop-receipt*), so every email that
       includes this looks consistent. Override this template to restyle every shop email at once. */

    /* Resets (from the email-boilerplate the original used). */
    html { font-size: 16px; }
    body {
        margin: 0; padding: 0; width: 100% !important; height: 100%;
        background: #f2f2f2; color: #222222;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;
    }
    img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; display: block; }
    a img { border: none; }
    table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    table td { border-collapse: collapse; }
    a { color: #222222; }

    /* Card: a centred white panel on the light canvas. */
    table.silvershop-email { width: 600px; max-width: 100%; margin: 0 auto; background: #ffffff; border: 1px solid #e5e5e5; }
    table.silvershop-email > tbody > tr > td, table.silvershop-email > tr > td { padding: 0; }
    .silvershop-email__content { width: 100%; background: #ffffff; text-align: left; }

    /* Brand header (shop name) with a heavy rule, then the email's main title below it. */
    .silvershop-email__title-cell { padding: 24px 28px 18px; }
    .silvershop-email__brand { display: block; margin: 0 0 14px; padding: 0 0 14px; font-size: 18px; font-weight: 700; line-height: 1.25; text-align: left; color: #222222; border-bottom: 2px solid #222222; }
    .silvershop-email__title { display: block; margin: 0; padding: 0; font-size: 22px; font-weight: 700; line-height: 1.25; text-align: left; text-transform: none; color: #222222; }

    .silvershop-email__intro { padding: 18px 28px; color: #444444; font-size: 14px; line-height: 1.6; }
    .silvershop-email__order { padding: 4px 28px 24px; }
    .silvershop-email__downloads { padding: 0 28px 24px; color: #222222; font-size: 14px; line-height: 1.6; }

    /* Order summary — flat, horizontal rules only. */
    .silvershop-receipt { width: 100%; border-collapse: collapse; background: transparent; border: 0; margin: 0 0 18px; }
    .silvershop-receipt h3 { margin: 0 0 8px; color: #222222; font-size: 15px; font-weight: 700; font-family: inherit; }
    .silvershop-receipt__cell { padding: 9px 10px; color: #222222; font-size: 13px; border: 0; border-bottom: 1px solid #ededed; text-align: left; vertical-align: top; }
    .silvershop-receipt__cell--head { font-weight: 700; color: #222222; background: transparent; border-bottom: 2px solid #222222; text-transform: none; font-size: 12px; letter-spacing: 0.02em; }
    .silvershop-receipt__cell a { color: #222222; text-decoration: underline; }
    .silvershop-receipt__cell a:hover { text-decoration: none; }
    .silvershop-receipt__row--summary { font-weight: 700; }
    .silvershop-receipt__cell--ordersummary { border-bottom: 1px solid #ededed; font-size: 1em; }
    .silvershop-receipt__row--modifier .silvershop-receipt__cell--label { text-align: right; font-weight: 400; color: #555555; }
    .silvershop-receipt__row--modifier .silvershop-receipt__cell--value { color: #555555; }
    .silvershop-receipt__row--total td { font-weight: 700; font-size: 14px; text-transform: none; border-top: 2px solid #222222; border-bottom: 0; }
    .silvershop-receipt__cell--right, .silvershop-receipt__cell--value { text-align: right; }
    .silvershop-receipt__cell--center { text-align: center; }
    .silvershop-receipt__cell--left, .silvershop-receipt__cell--head { text-align: left; }
    .silvershop-receipt--addresses td, .silvershop-receipt--addresses th { width: 50%; vertical-align: top; }
    .silvershop-receipt--outstanding .silvershop-receipt__cell { border-bottom: 0; }

    /* Line-item image: compact thumbnail, with a flat grey placeholder square when a line has no image. */
    .silvershop-receipt__cell--image { width: 52px; }
    .silvershop-receipt__image, .silvershop-receipt__no-image { display: block; width: 44px; height: 44px; }
    .silvershop-receipt__image { object-fit: cover; }
    .silvershop-receipt__no-image { background: #f2f2f2; }

    /* Message / warning. */
    .silvershop-message--warning { margin: 0 0 16px; padding: 10px 12px; color: #8a6d1c; border: 1px solid #e9d8a6; background: #fcf8e3; }

    @media only screen and (max-width: 620px) {
        table.silvershop-email { width: 100% !important; }
        .silvershop-email__title-cell, .silvershop-email__intro, .silvershop-email__order, .silvershop-email__downloads {
            padding-left: 18px !important; padding-right: 18px !important;
        }
    }
</style>
