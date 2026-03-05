const fs = require('fs');

const htmlPath = 'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/product/products.html';
let htmlContent = fs.readFileSync(htmlPath, 'utf8');

// The CSS starts at "    /* ══ CART ICON BADGE ══ */" and ends right before "    /* Navbar control size alignment across pages */"
const cssStart1 = htmlContent.indexOf('    /* ══ CART ICON BADGE ══ */');
const cssEnd1 = htmlContent.indexOf('    /* ══ OUT OF STOCK ══ */');
const cssStart2 = htmlContent.indexOf('    /* ══ CHECKOUT MODAL ══ */');
const cssEnd2 = htmlContent.indexOf('    /* Navbar control size alignment across pages */');

const css = htmlContent.substring(cssStart1, cssEnd1) + htmlContent.substring(cssStart2, cssEnd2);


// Replace the specific file names if needed, but since it's global let's leave as is.

const domStart = htmlContent.indexOf('  <!-- ══ CART DRAWER ══ -->');
const domEnd = htmlContent.lastIndexOf('  <script>');
const chatEnd = htmlContent.indexOf('  </div>\n  </div>\n\n  <script>');
let html = "";
if (chatEnd > -1) {
    html = htmlContent.substring(domStart, chatEnd + 15); // Include the closing divs
} else {
    const nextScript = htmlContent.indexOf('  <script>', domStart);
    html = htmlContent.substring(domStart, nextScript);
}

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
console.log("Created shared-cart.js without overlapping responsive styles.");
