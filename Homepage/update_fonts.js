const fs = require('fs');
const path = require('path');

const files = [
    'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/home/index.html',
    'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/product/products.html',
    'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/about/about.html',
    'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/services/services.html',
    'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/contact/contact.html',
    'c:/Users/cuart/Desktop/xampp/htdocs/CAL-ELITE-US/Homepage/shared-cart.js'
];

files.forEach(f => {
    if (fs.existsSync(f)) {
        let content = fs.readFileSync(f, 'utf8');

        // Replace the font link (this regex catches the Cormorant Garamond link)
        content = content.replace(
            /href=\"https:\/\/fonts\.googleapis\.com\/css2\?family=Cormorant\+Garamond:[^\"]+\"/g,
            'href=\"https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Raleway:wght@200;300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600&display=swap\"'
        );

        // Replace font families in CSS
        // Cormorant Garamond -> Montserrat
        content = content.replace(/'Cormorant Garamond',\\s*serif/g, "'Montserrat', sans-serif");
        content = content.replace(/Cormorant Garamond,\\s*serif/g, "'Montserrat', sans-serif");

        // DM Mono -> Inter
        content = content.replace(/'DM Mono',\\s*monospace/g, "'Inter', sans-serif");
        content = content.replace(/DM Mono,\\s*monospace/g, "'Inter', sans-serif");

        // Also, because Inter is not a monospace font, any letter-spacing tweaks might be large, but it should still look very premium and modern!

        fs.writeFileSync(f, content);
        console.log('Updated font changes in ' + path.basename(f));
    } else {
        console.log('File not found: ' + f);
    }
});
