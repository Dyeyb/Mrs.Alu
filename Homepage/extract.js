const fs = require('fs');

const indexPath = 'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/home/index.html';
const aboutPath = 'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/about/about.html';
const servicesPath = 'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/services/services.html';
const contactPath = 'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/contact/contact.html';

const filesToUpdate = [indexPath, aboutPath, servicesPath, contactPath];

for (const path of filesToUpdate) {
    let content = fs.readFileSync(path, 'utf8');

    // Add IDs to Orders button
    content = content.replace(
        /<button class="nav-orders-btn"[^>]*title="My Orders"[^>]*>/g,
        '<button class="nav-orders-btn" id="navOrdersBtn" title="My Orders" aria-label="My Orders" type="button">'
    );
    content = content.replace(
        /<span class="orders-badge"[^>]*>0<\/span>/g,
        '<span class="orders-badge" id="ordersBadge">0</span>'
    );

    // Add IDs to Cart button
    content = content.replace(
        /<button class="nav-cart-btn"[^>]*title="View Cart"[^>]*>/g,
        '<button class="nav-cart-btn" id="navCartBtn" title="View Cart" aria-label="Shopping Cart" type="button">'
    );
    content = content.replace(
        /<span class="cart-badge"[^>]*>0<\/span>/g,
        '<span class="cart-badge" id="cartBadge">0</span>'
    );

    // Add script inclusion at the end of body
    if (!content.includes('shared-cart.js')) {
        content = content.replace('</body>', '  <script src="../shared-cart.js"></script>\n</body>');
    }

    fs.writeFileSync(path, content);
}

console.log("Updated HTML files.");

const htmlPath = 'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/product/products.html';
let htmlContent = fs.readFileSync(htmlPath, 'utf8');

// The CSS starts at "    /* ══ CART ICON BADGE ══ */" and ends right before "    /* Navbar control size alignment across pages */"
const cssStart = htmlContent.indexOf('    /* ══ CART ICON BADGE ══ */');
const cssEnd = htmlContent.indexOf('    /* Navbar control size alignment across pages */');
const css = htmlContent.substring(cssStart, cssEnd);

// The DOM elements start at "  <!-- ══ CART DRAWER ══ -->" and ends at "  <script src="../Login/auth-guard.js" defer></script>" or something. Let's find "  <script>"
const domStart = htmlContent.indexOf('  <!-- ══ CART DRAWER ══ -->');
const domEnd = htmlContent.lastIndexOf('  <script>');
// But wait, there might be other things between the cart drawers and script. Let's look exactly at the end of the chat modal.
const chatEnd = htmlContent.indexOf('  </div>\n  </div>\n\n  <script>');
let html = "";
if (chatEnd > -1) {
    html = htmlContent.substring(domStart, chatEnd + 15);
} else {
    // Let's just find the first script tag after domStart
    const nextScript = htmlContent.indexOf('  <script>', domStart);
    html = htmlContent.substring(domStart, nextScript);
}

// The JS starts at "    // ══ CART SYSTEM ══" and ends at "  </script>"
const jsStart = htmlContent.indexOf('    // ══ CART SYSTEM ══');
const jsEnd = htmlContent.lastIndexOf('  </script>');
const js = htmlContent.substring(jsStart, jsEnd);

const addonContent = `
// ══ CART & ORDERS ADDON ══ //

const _cartCss = \`${css.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`;

const _cartHtml = \`${html.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`;

document.head.insertAdjacentHTML('beforeend', '<style>' + _cartCss + '</style>');
document.body.insertAdjacentHTML('beforeend', _cartHtml);

${js}
`;

fs.writeFileSync('c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/shared-cart.js', addonContent);
console.log("Created shared-cart.js.");
