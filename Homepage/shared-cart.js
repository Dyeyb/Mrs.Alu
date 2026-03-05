
// ══ CART & ORDERS ADDON ══ //

const _cartCss = `    /* ══ CART ICON BADGE ══ */
    .nav-cart-btn {
      position: relative;
      width: 34px;
      height: 34px;
      border-radius: 3px;
      border: 1px solid rgba(255, 255, 255, 0.07);
      background: var(--panel);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
      transition: var(--t);
      color: var(--fog);
    }

    .nav-cart-btn:hover {
      border-color: var(--gold-rim);
      background: rgba(184, 147, 42, 0.1);
      color: var(--gold-bright);
    }

    .cart-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      min-width: 17px;
      height: 17px;
      padding: 0 4px;
      background: linear-gradient(135deg, var(--gold), var(--gold-bright));
      border-radius: 9px;
      border: 1.5px solid var(--obsidian);
      font-family: 'DM Mono', monospace;
      font-size: 9px;
      font-weight: 700;
      color: var(--ink);
      display: none;
      align-items: center;
      justify-content: center;
      line-height: 1;
      animation: badgePop .25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .cart-badge.visible {
      display: flex;
    }

    @keyframes badgePop {
      from {
        transform: scale(0)
      }

      to {
        transform: scale(1)
      }
    }

    /* ══ CART DRAWER ══ */
    .cart-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(6px);
      z-index: 4000;
      opacity: 0;
      pointer-events: none;
      transition: opacity .35s ease;
    }

    .cart-overlay.open {
      opacity: 1;
      pointer-events: all;
    }

    .cart-drawer {
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      width: 420px;
      max-width: 95vw;
      background: #0e0e0e;
      border-left: 1px solid var(--gold-rim);
      z-index: 4001;
      display: flex;
      flex-direction: column;
      transform: translateX(100%);
      transition: transform .4s cubic-bezier(0.25, 1, 0.5, 1);
      box-shadow: -24px 0 80px rgba(0, 0, 0, 0.7);
    }

    .cart-drawer.open {
      transform: translateX(0);
    }

    .cart-drawer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--gold-bright), transparent);
    }

    /* Drawer header */
    .cart-head {
      padding: 24px 24px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(0, 0, 0, 0.3);
      flex-shrink: 0;
    }

    .cart-head-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .cart-head-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      font-weight: 700;
      color: var(--platinum);
    }

    .cart-head-title em {
      color: var(--gold-bright);
      font-style: italic;
    }

    .cart-item-count {
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--gold);
      opacity: 0.7;
      background: var(--gold-dim);
      border: 1px solid var(--gold-border);
      padding: 3px 8px;
      border-radius: 2px;
    }

    .cart-close-btn {
      width: 32px;
      height: 32px;
      border-radius: 3px;
      border: 1px solid rgba(255, 255, 255, 0.07);
      background: none;
      cursor: pointer;
      color: var(--smoke);
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--t);
    }

    .cart-close-btn:hover {
      background: rgba(220, 60, 60, 0.1);
      border-color: rgba(220, 60, 60, 0.3);
      color: #e06060;
    }

    /* Cart items list */
    .cart-items {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      scrollbar-width: thin;
      scrollbar-color: var(--gold) transparent;
    }

    .cart-items::-webkit-scrollbar {
      width: 3px;
    }

    .cart-items::-webkit-scrollbar-thumb {
      background: var(--gold);
      border-radius: 2px;
    }

    /* Empty state */
    .cart-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      gap: 16px;
      padding: 40px 20px;
    }

    .cart-empty-icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      border: 1px solid var(--gold-rim);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--gold);
      opacity: 0.4;
    }

    .cart-empty p {
      font-family: 'Cormorant Garamond', serif;
      font-size: 18px;
      font-style: italic;
      color: var(--smoke);
      text-align: center;
    }

    .cart-empty span {
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--dim);
    }

    /* Cart item card */
    .cart-item {
      display: flex;
      gap: 14px;
      padding: 14px;
      background: linear-gradient(145deg, #111, #161616);
      border: 1px solid rgba(184, 147, 42, 0.14);
      border-radius: 4px;
      margin-bottom: 10px;
      animation: cartItemIn .3s cubic-bezier(0.22, 1, 0.36, 1);
      position: relative;
      overflow: hidden;
    }

    .cart-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(184, 147, 42, 0.5), transparent);
    }

    @keyframes cartItemIn {
      from {
        opacity: 0;
        transform: translateX(20px)
      }

      to {
        opacity: 1;
        transform: none
      }
    }

    .cart-item-img {
      width: 72px;
      height: 60px;
      border-radius: 3px;
      overflow: hidden;
      flex-shrink: 0;
      border: 1px solid rgba(255, 255, 255, 0.06);
    }

    .cart-item-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.85);
    }

    .cart-item-info {
      flex: 1;
      min-width: 0;
    }

    .cart-item-cat {
      font-family: 'DM Mono', monospace;
      font-size: 7px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--gold);
      opacity: 0.65;
      margin-bottom: 4px;
    }

    .cart-item-name {
      font-family: 'Cormorant Garamond', serif;
      font-size: 16px;
      font-weight: 600;
      color: var(--platinum);
      margin-bottom: 6px;
      line-height: 1.2;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .cart-item-price {
      font-family: 'DM Mono', monospace;
      font-size: 10px;
      color: var(--gold);
      letter-spacing: 1px;
    }

    /* Quantity controls */
    .cart-item-controls {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 10px;
    }

    .qty-btn {
      width: 24px;
      height: 24px;
      border-radius: 2px;
      border: 1px solid rgba(184, 147, 42, 0.3);
      background: transparent;
      color: var(--gold-bright);
      font-size: 14px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--t);
      line-height: 1;
    }

    .qty-btn:hover {
      background: var(--gold-dim);
      border-color: var(--gold);
    }

    .qty-val {
      font-family: 'DM Mono', monospace;
      font-size: 11px;
      color: var(--platinum);
      min-width: 20px;
      text-align: center;
      letter-spacing: 1px;
    }

    /* Remove btn */
    .cart-item-remove {
      width: 26px;
      height: 26px;
      border-radius: 2px;
      border: none;
      background: transparent;
      color: rgba(220, 60, 60, 0.5);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--t);
      flex-shrink: 0;
      align-self: flex-start;
    }

    .cart-item-remove:hover {
      color: #e06060;
      background: rgba(220, 60, 60, 0.1);
    }

    /* Separator */
    .cart-sep {
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(184, 147, 42, 0.15), transparent);
      margin: 8px 0 16px;
    }

    /* Footer */
    .cart-foot {
      padding: 20px 24px 24px;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
      background: rgba(0, 0, 0, 0.25);
      flex-shrink: 0;
    }

    .cart-totals {
      margin-bottom: 18px;
    }

    .cart-total-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 6px 0;
    }

    .cart-total-label {
      font-family: 'DM Mono', monospace;
      font-size: 8.5px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--dim);
    }

    .cart-total-val {
      font-family: 'DM Mono', monospace;
      font-size: 10px;
      color: var(--silver);
      letter-spacing: 1px;
    }

    .cart-grand-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 0 0;
      border-top: 1px solid rgba(184, 147, 42, 0.18);
      margin-top: 8px;
    }

    .cart-grand-label {
      font-family: 'Cormorant Garamond', serif;
      font-size: 18px;
      font-weight: 600;
      color: var(--platinum);
    }

    .cart-grand-val {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      font-weight: 700;
      color: var(--gold-bright);
    }

    .cart-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .btn-checkout {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 3px;
      background: linear-gradient(135deg, var(--gold), var(--gold-bright) 60%, var(--gold-light));
      color: var(--ink);
      font-family: 'Raleway', sans-serif;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 3px;
      text-transform: uppercase;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: var(--t);
      box-shadow: 0 4px 28px rgba(212, 175, 55, 0.3);
    }

    .btn-checkout::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
      transition: left .5s;
    }

    .btn-checkout:hover::before {
      left: 100%;
    }

    .btn-checkout:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 36px rgba(212, 175, 55, 0.45);
    }

    .btn-clear-cart {
      width: 100%;
      padding: 10px;
      border: 1px solid rgba(220, 60, 60, 0.2);
      border-radius: 3px;
      background: transparent;
      color: rgba(220, 60, 60, 0.6);
      font-family: 'Raleway', sans-serif;
      font-size: 9px;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      cursor: pointer;
      transition: var(--t);
    }

    .btn-clear-cart:hover {
      background: rgba(220, 60, 60, 0.08);
      border-color: rgba(220, 60, 60, 0.4);
      color: #e06060;
    }

    /* Add to cart button (on product cards) */
    .card-cart-row {
      display: flex;
      gap: 8px;
      margin-top: auto;
    }

    .card-add-cart-btn {
      flex: 1;
      background: linear-gradient(135deg, var(--gold), var(--gold-bright));
      border: none;
      color: var(--ink);
      padding: 11px 0;
      border-radius: 2px;
      font-family: 'Raleway', sans-serif;
      font-size: 9px;
      font-weight: 700;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: var(--t);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
    }

    .card-add-cart-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
      transition: left .5s;
    }

    .card-add-cart-btn:hover::before {
      left: 100%;
    }

    .card-add-cart-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(184, 147, 42, 0.4);
    }

    .card-add-cart-btn.added {
      background: linear-gradient(135deg, #1a5c2a, #22703a);
      box-shadow: 0 4px 16px rgba(34, 112, 58, 0.35);
    }

    .card-view-btn {
      width: 40px;
      background: transparent;
      border: 1px solid rgba(184, 147, 42, 0.4);
      color: var(--gold-bright);
      border-radius: 2px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--t);
      flex-shrink: 0;
    }

    .card-view-btn:hover {
      background: var(--gold-dim);
      border-color: var(--gold);
    }

    /* ══ CHECKOUT MODAL ══ */
    .co-modal-bg {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(14px);
      z-index: 5000;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .co-modal-bg.open {
      display: flex;
    }

    .co-box {
      background: #0e0e12;
      border: 1px solid rgba(184, 147, 42, 0.25);
      border-radius: 6px;
      width: 100%;
      max-width: 780px;
      max-height: 92vh;
      overflow-y: auto;
      box-shadow: 0 60px 140px rgba(0, 0, 0, 0.95), 0 0 0 1px rgba(184, 147, 42, 0.1);
      animation: coIn .35s cubic-bezier(0.22, 1, 0.36, 1);
      position: relative;
      scrollbar-width: thin;
      scrollbar-color: #b8932a transparent;
    }

    .co-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, #d4a843, #e8c97a, #d4a843, transparent);
    }

    @keyframes coIn {
      from {
        opacity: 0;
        transform: scale(0.93) translateY(30px);
      }

      to {
        opacity: 1;
        transform: none;
      }
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(14px);
      }

      to {
        opacity: 1;
        transform: none;
      }
    }

    .co-head {
      padding: 24px 28px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(0, 0, 0, 0.3);
      position: sticky;
      top: 0;
      z-index: 10;
      backdrop-filter: blur(20px);
    }

    .co-head-left {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .co-head-icon {
      width: 40px;
      height: 40px;
      border-radius: 4px;
      background: linear-gradient(135deg, #b8932a, #d4a843);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #05050a;
      flex-shrink: 0;
    }

    .co-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 24px;
      font-weight: 700;
      color: #f2f0ea;
    }

    .co-title em {
      color: #d4a843;
      font-style: italic;
    }

    .co-subtitle {
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: rgba(184, 147, 42, 0.6);
      margin-top: 2px;
    }

    .co-close {
      width: 32px;
      height: 32px;
      border-radius: 3px;
      border: 1px solid rgba(255, 255, 255, 0.07);
      background: none;
      cursor: pointer;
      color: rgba(210, 205, 195, 0.4);
      font-size: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .3s;
    }

    .co-close:hover {
      background: rgba(220, 60, 60, 0.12);
      border-color: rgba(220, 60, 60, 0.3);
      color: #e06060;
    }

    .co-steps {
      display: flex;
      align-items: center;
      padding: 20px 28px 0;
      gap: 0;
    }

    .co-step {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
    }

    .co-step-num {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      border: 1.5px solid rgba(184, 147, 42, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'DM Mono', monospace;
      font-size: 11px;
      color: rgba(184, 147, 42, 0.4);
      flex-shrink: 0;
      transition: all .4s;
    }

    .co-step-label {
      font-family: 'DM Mono', monospace;
      font-size: 7.5px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(200, 200, 200, 0.28);
      transition: color .4s;
    }

    .co-step-line {
      flex: 1;
      height: 1px;
      background: rgba(184, 147, 42, 0.12);
      margin: 0 12px;
    }

    .co-step.active .co-step-num {
      border-color: #d4a843;
      background: linear-gradient(135deg, #b8932a, #d4a843);
      color: #05050a;
    }

    .co-step.active .co-step-label {
      color: #d4a843;
    }

    .co-step.done .co-step-num {
      border-color: #22703a;
      background: #22703a;
      color: #a8f0c6;
    }

    .co-step.done .co-step-label {
      color: rgba(168, 240, 198, 0.7);
    }

    .co-body {
      padding: 24px 28px;
    }

    .co-panel {
      display: none;
    }

    .co-panel.active {
      display: block;
      animation: fadeUp .3s ease;
    }

    .co-section-title {
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #b8932a;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .co-section-title::after {
      content: '';
      flex: 1;
      height: 1px;
      background: linear-gradient(90deg, rgba(184, 147, 42, 0.3), transparent);
    }

    .co-items-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 24px;
    }

    .co-item-row {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px;
      background: linear-gradient(145deg, #111, #161616);
      border: 1px solid rgba(184, 147, 42, 0.14);
      border-radius: 4px;
      position: relative;
      overflow: hidden;
    }

    .co-item-row::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(184, 147, 42, 0.4), transparent);
    }

    .co-item-img {
      width: 64px;
      height: 52px;
      border-radius: 3px;
      overflow: hidden;
      flex-shrink: 0;
      border: 1px solid rgba(255, 255, 255, 0.06);
    }

    .co-item-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.85);
    }

    .co-item-info {
      flex: 1;
      min-width: 0;
    }

    .co-item-cat {
      font-family: 'DM Mono', monospace;
      font-size: 7px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #b8932a;
      opacity: 0.65;
      margin-bottom: 3px;
    }

    .co-item-name {
      font-family: 'Cormorant Garamond', serif;
      font-size: 17px;
      font-weight: 600;
      color: #f2f0ea;
      margin-bottom: 4px;
      line-height: 1.2;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .co-item-sku {
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      color: rgba(210, 205, 195, 0.22);
    }

    .co-item-qty-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }

    .co-qty-btn {
      width: 28px;
      height: 28px;
      border-radius: 3px;
      border: 1px solid rgba(184, 147, 42, 0.35);
      background: transparent;
      color: #d4a843;
      font-size: 16px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .25s;
      line-height: 1;
    }

    .co-qty-btn:hover {
      background: rgba(184, 147, 42, 0.12);
      border-color: #d4a843;
    }

    .co-qty-val {
      font-family: 'DM Mono', monospace;
      font-size: 14px;
      color: #f2f0ea;
      min-width: 28px;
      text-align: center;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 3px;
      padding: 3px 0;
    }

    .co-item-remove {
      width: 28px;
      height: 28px;
      border: none;
      background: transparent;
      color: rgba(220, 60, 60, 0.45);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 3px;
      transition: all .25s;
    }

    .co-item-remove:hover {
      color: #e06060;
      background: rgba(220, 60, 60, 0.1);
    }

    .co-form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-bottom: 14px;
    }

    .co-form-row.three {
      grid-template-columns: 1fr 1fr 1fr;
    }

    .co-form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 14px;
    }

    .co-label {
      font-family: 'DM Mono', monospace;
      font-size: 7.5px;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #b8932a;
    }

    .co-label span {
      color: rgba(220, 60, 60, 0.7);
      margin-left: 2px;
    }

    .co-input,
    .co-select,
    .co-textarea {
      width: 100%;
      padding: 11px 13px;
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 3px;
      font-size: 13px;
      font-family: 'Raleway', sans-serif;
      background: #1a1a28;
      color: #f2f0ea;
      outline: none;
      transition: all .3s;
    }

    .co-select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none' stroke='%23b8932a' stroke-width='1.5'%3E%3Cpath d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      padding-right: 36px;
      cursor: pointer;
    }

    .co-select option {
      background: #0e0e12;
    }

    .co-textarea {
      height: 90px;
      resize: none;
    }

    .co-input:focus,
    .co-select:focus,
    .co-textarea:focus {
      border-color: rgba(184, 147, 42, 0.45);
      box-shadow: 0 0 0 3px rgba(184, 147, 42, 0.07);
    }

    .co-input::placeholder,
    .co-textarea::placeholder {
      color: rgba(210, 205, 195, 0.22);
    }

    .co-date-note {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 12px 14px;
      background: rgba(184, 147, 42, 0.06);
      border: 1px solid rgba(184, 147, 42, 0.18);
      border-radius: 3px;
      margin-top: 6px;
    }

    .co-date-note svg {
      flex-shrink: 0;
      margin-top: 1px;
      color: #b8932a;
      opacity: 0.7;
    }

    .co-date-note p {
      font-family: 'DM Mono', monospace;
      font-size: 8.5px;
      letter-spacing: 1px;
      color: rgba(210, 205, 195, 0.5);
      line-height: 1.6;
    }

    .co-date-note strong {
      color: #d4a843;
    }

    .co-payment-options {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 20px;
    }

    .co-pay-opt {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 16px;
      border: 1.5px solid rgba(255, 255, 255, 0.07);
      border-radius: 4px;
      cursor: pointer;
      transition: all .3s;
      position: relative;
      overflow: hidden;
    }

    .co-pay-opt::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(184, 147, 42, 0.06), transparent);
      opacity: 0;
      transition: opacity .3s;
    }

    .co-pay-opt:hover {
      border-color: rgba(184, 147, 42, 0.3);
    }

    .co-pay-opt:hover::before {
      opacity: 1;
    }

    .co-pay-opt.selected {
      border-color: #d4a843;
      background: rgba(184, 147, 42, 0.06);
    }

    .co-pay-opt.selected::before {
      opacity: 1;
    }

    .co-pay-radio {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      border: 1.5px solid rgba(255, 255, 255, 0.15);
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .3s;
    }

    .co-pay-opt.selected .co-pay-radio {
      border-color: #d4a843;
      background: #d4a843;
    }

    .co-pay-radio::after {
      content: '';
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #05050a;
      opacity: 0;
      transition: opacity .3s;
    }

    .co-pay-opt.selected .co-pay-radio::after {
      opacity: 1;
    }

    .co-pay-icon {
      width: 42px;
      height: 42px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.06);
      flex-shrink: 0;
    }

    .co-pay-info {
      flex: 1;
    }

    .co-pay-name {
      font-family: 'Raleway', sans-serif;
      font-size: 14px;
      font-weight: 600;
      color: #f2f0ea;
      margin-bottom: 3px;
    }

    .co-pay-desc {
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      letter-spacing: 1px;
      color: rgba(210, 205, 195, 0.35);
    }

    .co-pay-badge {
      font-family: 'DM Mono', monospace;
      font-size: 7px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 3px 8px;
      border-radius: 2px;
    }

    .co-pay-badge.recommend {
      background: rgba(184, 147, 42, 0.12);
      border: 1px solid rgba(184, 147, 42, 0.3);
      color: #d4a843;
    }

    .co-pay-badge.soon {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      color: rgba(200, 200, 200, 0.3);
    }

    .co-cod-notice {
      display: flex;
      gap: 12px;
      padding: 14px 16px;
      background: rgba(34, 112, 58, 0.08);
      border: 1px solid rgba(34, 112, 58, 0.2);
      border-radius: 3px;
      margin-top: 4px;
    }

    .co-cod-notice svg {
      flex-shrink: 0;
      color: #22703a;
      margin-top: 1px;
    }

    .co-cod-notice p {
      font-family: 'DM Mono', monospace;
      font-size: 8.5px;
      letter-spacing: 1px;
      color: rgba(168, 240, 198, 0.6);
      line-height: 1.6;
    }

    .co-summary-box {
      background: rgba(0, 0, 0, 0.25);
      border: 1px solid rgba(184, 147, 42, 0.12);
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 20px;
    }

    .co-summary-head {
      padding: 12px 16px;
      background: rgba(184, 147, 42, 0.05);
      border-bottom: 1px solid rgba(184, 147, 42, 0.1);
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #b8932a;
    }

    .co-summary-body {
      padding: 16px;
    }

    .co-summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }

    .co-summary-row:last-child {
      border-bottom: none;
    }

    .co-summary-label {
      font-family: 'DM Mono', monospace;
      font-size: 9px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: rgba(200, 200, 200, 0.4);
    }

    .co-summary-val {
      font-family: 'DM Mono', monospace;
      font-size: 10px;
      color: #c8c8c8;
    }

    .co-summary-val.highlight {
      color: #d4a843;
    }

    .co-summary-total-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 0 0;
      margin-top: 8px;
      border-top: 1px solid rgba(184, 147, 42, 0.2);
    }

    .co-summary-total-label {
      font-family: 'Cormorant Garamond', serif;
      font-size: 20px;
      font-weight: 600;
      color: #f2f0ea;
    }

    .co-summary-total-val {
      font-family: 'Cormorant Garamond', serif;
      font-size: 26px;
      font-weight: 700;
      color: #d4a843;
    }

    .co-confirm-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 20px;
    }

    .co-confirm-block {
      background: rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 4px;
      padding: 14px;
    }

    .co-confirm-block-title {
      font-family: 'DM Mono', monospace;
      font-size: 7px;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: rgba(184, 147, 42, 0.6);
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .co-confirm-block-title svg {
      flex-shrink: 0;
    }

    .co-confirm-detail {
      font-size: 12.5px;
      color: rgba(200, 200, 200, 0.7);
      line-height: 1.6;
    }

    .co-confirm-detail strong {
      color: #f2f0ea;
      font-weight: 600;
    }

    .co-terms {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 14px;
      background: rgba(184, 147, 42, 0.04);
      border: 1px solid rgba(184, 147, 42, 0.12);
      border-radius: 3px;
      margin-bottom: 20px;
      cursor: pointer;
    }

    .co-check {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
      border-radius: 2px;
      border: 1.5px solid rgba(184, 147, 42, 0.4);
      background: transparent;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .3s;
      margin-top: 1px;
    }

    .co-check.checked {
      background: linear-gradient(135deg, #b8932a, #d4a843);
      border-color: #d4a843;
    }

    .co-terms-text {
      font-family: 'DM Mono', monospace;
      font-size: 8.5px;
      letter-spacing: 1px;
      color: rgba(210, 205, 195, 0.45);
      line-height: 1.7;
    }

    .co-terms-text a {
      color: #b8932a;
      text-decoration: none;
    }

    .co-foot {
      padding: 16px 28px 24px;
      border-top: 1px solid rgba(255, 255, 255, 0.04);
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      bottom: 0;
      background: rgba(14, 14, 18, 0.97);
      backdrop-filter: blur(20px);
    }

    .co-foot-left {
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(200, 200, 200, 0.28);
    }

    .co-foot-left strong {
      display: block;
      font-family: 'Cormorant Garamond', serif;
      font-size: 20px;
      letter-spacing: 0;
      font-weight: 700;
      color: #d4a843;
      margin-top: 2px;
    }

    .co-foot-btns {
      display: flex;
      gap: 10px;
    }

    .co-btn-back {
      padding: 10px 22px;
      border-radius: 3px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: none;
      font-family: 'Raleway', sans-serif;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      cursor: pointer;
      color: rgba(210, 205, 195, 0.4);
      transition: all .3s;
    }

    .co-btn-back:hover {
      border-color: rgba(255, 255, 255, 0.15);
      color: rgba(210, 205, 195, 0.7);
    }

    .co-btn-next {
      padding: 12px 30px;
      border-radius: 3px;
      border: none;
      background: linear-gradient(135deg, #b8932a, #d4a843 60%, #e8c97a);
      color: #05050a;
      font-family: 'Raleway', sans-serif;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      cursor: pointer;
      transition: all .3s;
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 22px rgba(184, 147, 42, 0.3);
    }

    .co-btn-next::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left .5s;
    }

    .co-btn-next:hover::before {
      left: 100%;
    }

    .co-btn-next:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 36px rgba(184, 147, 42, 0.45);
    }

    .co-btn-next:disabled {
      opacity: .4;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .co-success {
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 60px 28px;
      text-align: center;
    }

    .co-success.show {
      display: flex;
      animation: fadeUp .5s ease;
    }

    .co-success-ring {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border: 2px solid rgba(34, 112, 58, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 24px;
      position: relative;
    }

    .co-success-ring::before {
      content: '';
      position: absolute;
      inset: -8px;
      border-radius: 50%;
      border: 1px solid rgba(34, 112, 58, 0.15);
    }

    .co-success-check {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: linear-gradient(135deg, #134d27, #22703a);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #a8f0c6;
    }

    .co-success h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 30px;
      font-weight: 700;
      color: #f2f0ea;
      margin-bottom: 8px;
    }

    .co-success h2 em {
      color: #d4a843;
      font-style: italic;
    }

    .co-success p {
      font-family: 'DM Mono', monospace;
      font-size: 9px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(200, 200, 200, 0.35);
      line-height: 1.7;
      max-width: 340px;
    }

    .co-order-ref {
      margin: 20px 0;
      padding: 14px 28px;
      background: rgba(184, 147, 42, 0.07);
      border: 1px solid rgba(184, 147, 42, 0.2);
      border-radius: 3px;
    }

    .co-order-ref-label {
      font-family: 'DM Mono', monospace;
      font-size: 7px;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #b8932a;
      opacity: .7;
      margin-bottom: 4px;
    }

    .co-order-ref-num {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      font-weight: 700;
      color: #d4a843;
      letter-spacing: 3px;
    }

    @media(max-width:640px) {
      .co-form-row {
        grid-template-columns: 1fr;
      }

      .co-form-row.three {
        grid-template-columns: 1fr;
      }

      .co-confirm-grid {
        grid-template-columns: 1fr;
      }

      .co-foot {
        flex-direction: column;
        gap: 14px;
        align-items: stretch;
      }

      .co-foot-btns {
        flex-direction: column;
      }
    }

    /* ══ ORDERS BUTTON ══ */
    .nav-orders-btn {
      width: 34px;
      height: 34px;
      border-radius: 3px;
      border: 1px solid rgba(168, 131, 44, 0.52);
      background: #05070d;
      color: #626b84;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      transition: var(--t);
      flex-shrink: 0;
      box-shadow: inset 0 0 0 1px rgba(255, 214, 112, 0.04);
    }

    .nav-orders-btn:hover {
      border-color: rgba(191, 152, 57, 0.72);
      background: #080b12;
      color: #747f9a;
    }

    .orders-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: linear-gradient(135deg, #b8932a, #d4a843);
      color: #05050a;
      font-family: 'DM Mono', monospace;
      font-size: 9px;
      font-weight: 700;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transform: scale(0);
      transition: var(--t);
    }

    .orders-badge.visible {
      opacity: 1;
      transform: scale(1);
    }

    /* ══ ORDERS DRAWER ══ */
    .orders-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(8px);
      z-index: 2000;
      opacity: 0;
      pointer-events: none;
      transition: opacity .35s;
    }

    .orders-overlay.open {
      opacity: 1;
      pointer-events: all;
    }

    .orders-drawer {
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      width: 420px;
      max-width: 100vw;
      background: linear-gradient(180deg, #0b0b10 0%, #0e0e14 100%);
      border-left: 1px solid rgba(184, 147, 42, 0.18);
      z-index: 2001;
      display: flex;
      flex-direction: column;
      transform: translateX(100%);
      transition: transform .38s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .orders-drawer.open {
      transform: translateX(0);
    }

    .orders-head {
      padding: 22px 24px 18px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      background: rgba(0, 0, 0, 0.3);
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      backdrop-filter: blur(20px);
    }

    .orders-head-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .orders-head-icon {
      width: 36px;
      height: 36px;
      border-radius: 4px;
      background: linear-gradient(135deg, #b8932a, #d4a843);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #05050a;
      flex-shrink: 0;
    }

    .orders-head-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      font-weight: 700;
      color: var(--platinum);
    }

    .orders-head-sub {
      font-family: 'DM Mono', monospace;
      font-size: 7.5px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--gold);
      opacity: .55;
      margin-top: 1px;
    }

    .orders-close {
      width: 30px;
      height: 30px;
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 3px;
      background: none;
      cursor: pointer;
      color: rgba(210, 205, 195, 0.4);
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .25s;
    }

    .orders-close:hover {
      background: rgba(220, 60, 60, 0.1);
      border-color: rgba(220, 60, 60, 0.3);
      color: #e06060;
    }

    .orders-body {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .orders-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      flex: 1;
      gap: 12px;
      color: var(--smoke);
      padding: 60px 0;
    }

    .orders-empty svg {
      opacity: .25;
    }

    .orders-empty p {
      font-family: 'DM Mono', monospace;
      font-size: 9px;
      letter-spacing: 2px;
      text-transform: uppercase;
      opacity: .4;
    }

    /* order card */
    .ord-card {
      background: linear-gradient(145deg, #111, #161616);
      border: 1px solid rgba(184, 147, 42, 0.14);
      border-radius: 5px;
      overflow: hidden;
      cursor: pointer;
      transition: border-color .25s;
    }

    .ord-card:hover {
      border-color: rgba(184, 147, 42, 0.35);
    }

    .ord-card-head {
      padding: 14px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    }

    .ord-ref {
      font-family: 'DM Mono', monospace;
      font-size: 11px;
      color: var(--gold-bright);
      letter-spacing: 1.5px;
    }

    .ord-date {
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      color: var(--smoke);
      opacity: .55;
    }

    .ord-status-pill {
      font-family: 'DM Mono', monospace;
      font-size: 7.5px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 3px 9px;
      border-radius: 2px;
      font-weight: 600;
    }

    .pill-pending {
      background: rgba(184, 147, 42, 0.12);
      color: #d4a843;
      border: 1px solid rgba(184, 147, 42, 0.25);
    }

    .pill-confirmed {
      background: rgba(59, 130, 246, 0.1);
      color: #60a5fa;
      border: 1px solid rgba(59, 130, 246, 0.2);
    }

    .pill-transit {
      background: rgba(168, 85, 247, 0.1);
      color: #c084fc;
      border: 1px solid rgba(168, 85, 247, 0.2);
    }

    .pill-nearby {
      background: rgba(251, 146, 60, 0.12);
      color: #fb923c;
      border: 1px solid rgba(251, 146, 60, 0.25);
      animation: ribbonPulse 1.5s infinite;
    }

    .pill-delivered {
      background: rgba(34, 112, 58, 0.12);
      color: #4ade80;
      border: 1px solid rgba(34, 112, 58, 0.25);
    }

    .ord-card-body {
      padding: 12px 16px;
    }

    .ord-items-preview {
      font-family: 'DM Mono', monospace;
      font-size: 8.5px;
      color: var(--ash);
      letter-spacing: .5px;
      margin-bottom: 10px;
      line-height: 1.6;
    }

    .ord-actions {
      display: flex;
      gap: 8px;
    }

    .ord-btn {
      flex: 1;
      padding: 8px 0;
      border-radius: 2px;
      border: 1px solid rgba(184, 147, 42, 0.3);
      background: transparent;
      color: var(--gold-bright);
      font-family: 'DM Mono', monospace;
      font-size: 7.5px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      cursor: pointer;
      transition: all .25s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }

    .ord-btn:hover {
      background: rgba(184, 147, 42, 0.1);
      border-color: var(--gold);
    }

    .ord-btn.chat-btn {
      border-color: rgba(99, 202, 183, 0.3);
      color: rgba(99, 202, 183, 0.8);
    }

    .ord-btn.chat-btn:hover {
      background: rgba(99, 202, 183, 0.08);
      border-color: rgba(99, 202, 183, 0.6);
    }

    /* timeline */
    .ord-timeline {
      padding: 14px 16px;
      border-top: 1px solid rgba(255, 255, 255, 0.04);
    }

    .tl-step {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      position: relative;
    }

    .tl-step:not(:last-child)::after {
      content: '';
      position: absolute;
      left: 10px;
      top: 22px;
      bottom: -6px;
      width: 1px;
      background: rgba(255, 255, 255, 0.06);
    }

    .tl-dot {
      width: 21px;
      height: 21px;
      border-radius: 50%;
      border: 2px solid rgba(255, 255, 255, 0.1);
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 1px;
    }

    .tl-dot.done {
      background: linear-gradient(135deg, #134d27, #22703a);
      border-color: #22703a;
    }

    .tl-dot.active {
      background: linear-gradient(135deg, #b8932a, #d4a843);
      border-color: #d4a843;
      animation: tlPulse 1.8s ease-in-out infinite;
    }

    @keyframes tlPulse {

      0%,
      100% {
        box-shadow: 0 0 0 0 rgba(212, 168, 67, 0.4)
      }

      50% {
        box-shadow: 0 0 0 6px rgba(212, 168, 67, 0)
      }
    }

    .tl-info {
      padding-bottom: 14px;
    }

    .tl-label {
      font-family: 'DM Mono', monospace;
      font-size: 8.5px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--platinum);
    }

    .tl-label.dim {
      color: var(--smoke);
      opacity: .4;
    }

    .tl-sublabel {
      font-family: 'DM Mono', monospace;
      font-size: 7.5px;
      color: var(--smoke);
      opacity: .4;
      margin-top: 2px;
    }

    /* ══ TRACKING MODAL ══ */
    .track-modal {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 3000;
      align-items: center;
      justify-content: center;
      padding: 20px;
      background: rgba(0, 0, 0, 0.88);
      backdrop-filter: blur(14px);
    }

    .track-modal.open {
      display: flex;
    }

    .track-box {
      background: #0c0c10;
      border: 1px solid rgba(184, 147, 42, 0.22);
      border-radius: 8px;
      width: 100%;
      max-width: 700px;
      max-height: 92vh;
      overflow: hidden;
      box-shadow: 0 60px 140px rgba(0, 0, 0, 0.95);
      animation: coIn .35s cubic-bezier(0.22, 1, 0.36, 1);
      display: flex;
      flex-direction: column;
    }

    .track-head {
      padding: 18px 22px;
      background: rgba(0, 0, 0, 0.3);
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .track-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 20px;
      font-weight: 700;
      color: var(--platinum);
    }

    .track-ref-tag {
      font-family: 'DM Mono', monospace;
      font-size: 8px;
      letter-spacing: 2px;
      color: var(--gold);
      opacity: .7;
      margin-top: 2px;
    }

    #trackMap {
      width: 100%;
      height: 340px;
      background: #0d1117;
    }

    .track-info-bar {
      padding: 14px 22px;
      background: rgba(0, 0, 0, 0.2);
      border-top: 1px solid rgba(255, 255, 255, 0.04);
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 12px;
    }

    .track-stat {
      text-align: center;
    }

    .track-stat-val {
      font-family: 'Cormorant Garamond', serif;
      font-size: 20px;
      font-weight: 700;
      color: var(--gold-bright);
    }

    .track-stat-lbl {
      font-family: 'DM Mono', monospace;
      font-size: 7px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--smoke);
      opacity: .5;
      margin-top: 2px;
    }

    /* proximity alert banner */
    .prox-alert {
      display: none;
      position: fixed;
      bottom: 80px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 9999;
      background: linear-gradient(135deg, #7c2d12, #9a3412);
      border: 1px solid rgba(251, 146, 60, 0.5);
      border-radius: 6px;
      padding: 14px 22px;
      max-width: 380px;
      width: calc(100% - 40px);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
      animation: coIn .4s cubic-bezier(0.22, 1, 0.36, 1);
      font-family: 'DM Mono', monospace;
    }

    .prox-alert.show {
      display: block;
    }

    .prox-alert-title {
      font-size: 10px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #fb923c;
      font-weight: 700;
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .prox-alert-body {
      font-size: 8.5px;
      letter-spacing: 1px;
      color: rgba(255, 200, 150, 0.7);
      line-height: 1.5;
    }

    .prox-dismiss {
      position: absolute;
      top: 10px;
      right: 12px;
      background: none;
      border: none;
      color: rgba(255, 200, 150, 0.5);
      font-size: 16px;
      cursor: pointer;
    }

    /* ══ COURIER CHAT ══ */
    .chat-fab {
      position: fixed;
      bottom: 28px;
      right: 28px;
      z-index: 4000;
      width: 54px;
      height: 54px;
      border-radius: 50%;
      background: linear-gradient(135deg, #134d27, #22703a);
      border: none;
      cursor: pointer;
      box-shadow: 0 8px 28px rgba(34, 112, 58, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      color: #a8f0c6;
      animation: fabBounce .6s cubic-bezier(0.22, 1, 0.36, 1);
      transition: transform .25s, box-shadow .25s;
    }

    .chat-fab.visible {
      display: flex;
    }

    .chat-fab:hover {
      transform: scale(1.08);
      box-shadow: 0 12px 36px rgba(34, 112, 58, 0.6);
    }

    @keyframes fabBounce {
      0% {
        transform: scale(0)
      }

      60% {
        transform: scale(1.1)
      }

      100% {
        transform: scale(1)
      }
    }

    .chat-fab-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      background: #fb923c;
      font-family: 'DM Mono', monospace;
      font-size: 9px;
      color: #000;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      animation: ribbonPulse 1.2s infinite;
    }

    .chat-window {
      position: fixed;
      bottom: 92px;
      right: 24px;
      z-index: 4001;
      width: 320px;
      background: #0e0e14;
      border: 1px solid rgba(99, 202, 183, 0.2);
      border-radius: 8px;
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8);
      display: none;
      flex-direction: column;
      max-height: 480px;
      overflow: hidden;
      animation: coIn .3s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .chat-window.open {
      display: flex;
    }

    .chat-win-head {
      padding: 12px 16px;
      background: rgba(0, 0, 0, 0.4);
      border-bottom: 1px solid rgba(99, 202, 183, 0.12);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .chat-courier-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .chat-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: linear-gradient(135deg, #134d27, #22703a);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Cormorant Garamond', serif;
      font-size: 16px;
      font-weight: 700;
      color: #a8f0c6;
      flex-shrink: 0;
    }

    .chat-courier-name {
      font-family: 'Raleway', sans-serif;
      font-size: 12px;
      font-weight: 600;
      color: var(--platinum);
    }

    .chat-courier-status {
      font-family: 'DM Mono', monospace;
      font-size: 7.5px;
      letter-spacing: 1.5px;
      color: rgba(74, 222, 128, 0.7);
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .online-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #4ade80;
      animation: ribbonPulse 1.5s infinite;
    }

    .chat-win-close {
      background: none;
      border: none;
      color: var(--smoke);
      cursor: pointer;
      font-size: 18px;
      opacity: .5;
    }

    .chat-win-close:hover {
      opacity: 1;
    }

    .chat-msgs {
      flex: 1;
      overflow-y: auto;
      padding: 12px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-height: 180px;
    }

    .chat-msg {
      max-width: 80%;
      padding: 8px 12px;
      border-radius: 6px;
      font-family: 'DM Mono', monospace;
      font-size: 10px;
      line-height: 1.5;
      letter-spacing: .3px;
    }

    .chat-msg.them {
      background: rgba(99, 202, 183, 0.1);
      border: 1px solid rgba(99, 202, 183, 0.18);
      color: rgba(210, 205, 195, 0.85);
      align-self: flex-start;
      border-radius: 6px 6px 6px 2px;
    }

    .chat-msg.me {
      background: rgba(184, 147, 42, 0.12);
      border: 1px solid rgba(184, 147, 42, 0.22);
      color: rgba(232, 201, 122, 0.9);
      align-self: flex-end;
      border-radius: 6px 6px 2px 6px;
    }

    .chat-msg-time {
      font-size: 7px;
      opacity: .35;
      margin-top: 3px;
    }

    .chat-input-row {
      display: flex;
      gap: 8px;
      padding: 10px 12px;
      border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .chat-input {
      flex: 1;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 4px;
      padding: 8px 10px;
      font-family: 'DM Mono', monospace;
      font-size: 10px;
      color: var(--platinum);
      outline: none;
      transition: border-color .25s;
    }

    .chat-input:focus {
      border-color: rgba(99, 202, 183, 0.4);
    }

    .chat-send {
      width: 34px;
      height: 34px;
      border-radius: 4px;
      border: none;
      background: linear-gradient(135deg, #134d27, #22703a);
      color: #a8f0c6;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .25s;
      flex-shrink: 0;
    }

    .chat-send:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(34, 112, 58, 0.4);
    }

`;

const _cartHtml = `  <!-- ══ CART DRAWER ══ -->
  <div class="cart-overlay" id="cartOverlay"></div>
  <div class="cart-drawer" id="cartDrawer">
    <div class="cart-head">
      <div class="cart-head-left">
        <div class="cart-head-title">Your <em>Cart</em></div>
        <span class="cart-item-count" id="cartItemCount">0 items</span>
      </div>
      <button class="cart-close-btn" id="cartCloseBtn">×</button>
    </div>
    <div class="cart-items" id="cartItems">
      <div class="cart-empty" id="cartEmpty">
        <div class="cart-empty-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.3"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <p>Your cart is empty</p>
        <span>Add products to get started</span>
      </div>
    </div>
    <div class="cart-foot" id="cartFoot" style="display:none;">
      <div class="cart-totals">
        <div class="cart-total-row">
          <span class="cart-total-label">Subtotal</span>
          <span class="cart-total-val" id="cartSubtotal">₱0</span>
        </div>
        <div class="cart-total-row">
          <span class="cart-total-label">VAT (12%)</span>
          <span class="cart-total-val" id="cartVat">₱0</span>
        </div>
        <div class="cart-grand-row">
          <span class="cart-grand-label">Total</span>
          <span class="cart-grand-val" id="cartTotal">₱0</span>
        </div>
      </div>
      <div class="cart-actions">
        <button class="btn-checkout" id="btnCheckout">
          Request Quote for Cart
        </button>
        <button class="btn-clear-cart" id="btnClearCart">Clear All Items</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <!-- CHECKOUT / ORDER MODAL -->
  <div class="co-modal-bg" id="checkoutModal">
    <div class="co-box" id="coBox">
      <!-- Header -->
      <div class="co-head">
        <div class="co-head-left">
          <div class="co-head-icon">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div>
            <div class="co-title">Place <em>Order</em></div>
            <div class="co-subtitle">Cal Elite Builders · Secure Checkout</div>
          </div>
        </div>
        <button class="co-close" id="coClose">×</button>
      </div>
      <!-- Steps -->
      <div class="co-steps">
        <div class="co-step active" id="coStep1">
          <div class="co-step-num">1</div>
          <div class="co-step-label">Review</div>
        </div>
        <div class="co-step-line"></div>
        <div class="co-step" id="coStep2">
          <div class="co-step-num">2</div>
          <div class="co-step-label">Delivery</div>
        </div>
        <div class="co-step-line"></div>
        <div class="co-step" id="coStep3">
          <div class="co-step-num">3</div>
          <div class="co-step-label">Payment</div>
        </div>
        <div class="co-step-line"></div>
        <div class="co-step" id="coStep4">
          <div class="co-step-num">4</div>
          <div class="co-step-label">Confirm</div>
        </div>
      </div>
      <!-- Body -->
      <div class="co-body">
        <!-- PANEL 1: Review Items -->
        <div class="co-panel active" id="coPanel1">
          <div class="co-section-title">Review Your Items</div>
          <div class="co-items-list" id="coItemsList"></div>
          <div class="co-section-title">Special Instructions</div>
          <div class="co-form-group">
            <label class="co-label">Order Notes (optional)</label>
            <textarea class="co-textarea" id="coNotes"
              placeholder="Any special requirements, delivery notes, or product specifications…"></textarea>
          </div>
        </div>
        <!-- PANEL 2: Delivery Details -->
        <div class="co-panel" id="coPanel2">
          <div class="co-section-title">Contact Information</div>
          <div class="co-form-row">
            <div class="co-form-group">
              <label class="co-label">Full Name <span>*</span></label>
              <input type="text" class="co-input" id="coFullName" placeholder="Your full name">
            </div>
            <div class="co-form-group">
              <label class="co-label">Phone / WhatsApp <span>*</span></label>
              <input type="text" class="co-input" id="coPhone" placeholder="+63 xxx xxx xxxx">
            </div>
          </div>
          <div class="co-form-group">
            <label class="co-label">Email Address</label>
            <input type="email" class="co-input" id="coEmail" placeholder="your@email.com">
          </div>
          <div class="co-section-title" style="margin-top:8px;">Delivery Address</div>
          <div class="co-form-group">
            <label class="co-label">Street / Building / Unit <span>*</span></label>
            <input type="text" class="co-input" id="coStreet" placeholder="House No., Street Name, Subdivision">
          </div>
          <div class="co-form-row three">
            <div class="co-form-group">
              <label class="co-label">Barangay <span>*</span></label>
              <input type="text" class="co-input" id="coBrgy" placeholder="Barangay">
            </div>
            <div class="co-form-group">
              <label class="co-label">City / Municipality <span>*</span></label>
              <input type="text" class="co-input" id="coCity" placeholder="City or Municipality">
            </div>
            <div class="co-form-group">
              <label class="co-label">Province <span>*</span></label>
              <input type="text" class="co-input" id="coProvince" placeholder="Province">
            </div>
          </div>
          <div class="co-form-row">
            <div class="co-form-group">
              <label class="co-label">ZIP Code</label>
              <input type="text" class="co-input" id="coZip" placeholder="0000" maxlength="4">
            </div>
            <div class="co-form-group">
              <label class="co-label">Landmark (optional)</label>
              <input type="text" class="co-input" id="coLandmark" placeholder="Near, across from…">
            </div>
          </div>
          <div class="co-section-title" style="margin-top:8px;">Preferred Delivery Date</div>
          <div class="co-form-row">
            <div class="co-form-group">
              <label class="co-label">Requested Delivery Date <span>*</span></label>
              <input type="date" class="co-input" id="coDelivDate">
            </div>
            <div class="co-form-group">
              <label class="co-label">Preferred Time Slot</label>
              <select class="co-select" id="coTimeSlot">
                <option value="">Select time slot</option>
                <option value="morning">Morning (8:00 AM – 12:00 PM)</option>
                <option value="afternoon">Afternoon (12:00 PM – 5:00 PM)</option>
                <option value="anytime">Any Time</option>
              </select>
            </div>
          </div>
          <div class="co-date-note">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p><strong>Estimated Delivery:</strong> Orders within Metro Manila are typically delivered within
              <strong>2–3 business days</strong>. Provincial orders may take <strong>5–7 business days</strong>. Our
              team will confirm the exact delivery schedule via call or WhatsApp.
            </p>
          </div>
        </div>
        <!-- PANEL 3: Payment -->
        <div class="co-panel" id="coPanel3">
          <div class="co-section-title">Choose Payment Method</div>
          <div class="co-payment-options">
            <div class="co-pay-opt selected" id="payOptCOD" onclick="selectPayment('cod')">
              <div class="co-pay-radio"></div>
              <div class="co-pay-icon">💵</div>
              <div class="co-pay-info">
                <div class="co-pay-name">Cash on Delivery</div>
                <div class="co-pay-desc">Pay in cash when your order arrives</div>
              </div>
              <div class="co-pay-badge recommend">Recommended</div>
            </div>
            <div class="co-pay-opt" id="payOptGcash" onclick="selectPayment('gcash')">
              <div class="co-pay-radio"></div>
              <div class="co-pay-icon">📱</div>
              <div class="co-pay-info">
                <div class="co-pay-name">GCash / Maya</div>
                <div class="co-pay-desc">Mobile wallet — payment link sent via WhatsApp</div>
              </div>
              <div class="co-pay-badge recommend" style="opacity:0.6;">Available</div>
            </div>
            <div class="co-pay-opt" id="payOptBank" onclick="selectPayment('bank')">
              <div class="co-pay-radio"></div>
              <div class="co-pay-icon">🏦</div>
              <div class="co-pay-info">
                <div class="co-pay-name">Bank Transfer</div>
                <div class="co-pay-desc">BDO / BPI — account details emailed upon confirmation</div>
              </div>
              <div class="co-pay-badge recommend" style="opacity:0.6;">Available</div>
            </div>
            <div class="co-pay-opt" id="payOptQuote" onclick="selectPayment('quote')"
              style="opacity:0.6; pointer-events:none;">
              <div class="co-pay-radio"></div>
              <div class="co-pay-icon">📋</div>
              <div class="co-pay-info">
                <div class="co-pay-name">Request Invoice First</div>
                <div class="co-pay-desc">Get a formal quotation before committing</div>
              </div>
              <div class="co-pay-badge soon">Coming Soon</div>
            </div>
          </div>
          <div class="co-cod-notice" id="coCodNotice">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p><strong style="color:#a8f0c6;">Cash on Delivery</strong> — No payment required upfront. Our delivery team
              will collect payment upon successful delivery. Please prepare the <strong>exact amount</strong> if
              possible.</p>
          </div>
        </div>
        <!-- PANEL 4: Confirm Order -->
        <div class="co-panel" id="coPanel4">
          <div class="co-section-title">Order Summary</div>
          <div class="co-summary-box">
            <div class="co-summary-head">Items · Subtotal · Fees</div>
            <div class="co-summary-body" id="coSummaryBody"></div>
          </div>
          <div class="co-confirm-grid">
            <div class="co-confirm-block">
              <div class="co-confirm-block-title"><svg width="12" height="12" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>Deliver To</div>
              <div class="co-confirm-detail" id="coConfirmAddress">—</div>
            </div>
            <div class="co-confirm-block">
              <div class="co-confirm-block-title"><svg width="12" height="12" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>Delivery Date</div>
              <div class="co-confirm-detail" id="coConfirmDate">—</div>
            </div>
            <div class="co-confirm-block">
              <div class="co-confirm-block-title"><svg width="12" height="12" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>Payment</div>
              <div class="co-confirm-detail" id="coConfirmPayment">—</div>
            </div>
            <div class="co-confirm-block">
              <div class="co-confirm-block-title"><svg width="12" height="12" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>Contact</div>
              <div class="co-confirm-detail" id="coConfirmContact">—</div>
            </div>
          </div>
          <div class="co-terms" id="coTermsRow" onclick="toggleTerms()">
            <div class="co-check" id="coCheck">
              <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"
                id="coCheckIcon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div class="co-terms-text">I confirm that the order details above are correct. I understand that Cal Elite
              Builders will contact me to finalize delivery scheduling and pricing. I agree to the <a href="#"
                onclick="return false;">Terms &amp; Conditions</a> and <a href="#" onclick="return false;">Privacy
                Policy</a>.</div>
          </div>
        </div>
      </div><!-- end co-body -->
      <!-- Success Screen -->
      <div class="co-success" id="coSuccess">
        <div class="co-success-ring">
          <div class="co-success-check">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        <h2>Order <em>Confirmed!</em></h2>
        <div class="co-order-ref">
          <div class="co-order-ref-label">Order Reference</div>
          <div class="co-order-ref-num" id="coOrderRef">#CE-000000</div>
        </div>
        <p>Thank you for your order. Our team will reach out within<br><strong style="color:#d4a843;">24 hours</strong>
          to confirm delivery details.</p>
      </div>
      <!-- Footer -->
      <div class="co-foot" id="coFoot">
        <div class="co-foot-left">
          Total Amount
          <strong id="coFootTotal">₱0</strong>
        </div>
        <div class="co-foot-btns">
          <button class="co-btn-back" id="coBtnBack" style="display:none" onclick="coBack()">← Back</button>
          <button class="co-btn-next" id="coBtnNext" onclick="coNext()">Review Items →</button>
        </div>
      </div>
    </div><!-- end co-box -->
  </div><!-- end checkoutModal -->

  <!-- ══ ORDERS DRAWER ══ -->
  <div class="orders-overlay" id="ordersOverlay"></div>
  <div class="orders-drawer" id="ordersDrawer">
    <div class="orders-head">
      <div class="orders-head-left">
        <div class="orders-head-icon">
          <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6M9 16h4" />
          </svg>
        </div>
        <div>
          <div class="orders-head-title">My <em style="color:var(--gold-bright);font-style:italic;">Orders</em></div>
          <div class="orders-head-sub">Track your deliveries</div>
        </div>
      </div>
      <button class="orders-close" id="ordersClose">×</button>
    </div>
    <div class="orders-body" id="ordersBody">
      <div class="orders-empty" id="ordersEmpty">
        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
        </svg>
        <p>No orders yet</p>
      </div>
    </div>
  </div>

  <!-- ══ LIVE TRACKING MODAL ══ -->
  <div class="track-modal" id="trackModal">
    <div class="track-box">
      <div class="track-head">
        <div>
          <div class="track-title">Live <em style="color:var(--gold-bright);font-style:italic;">Tracking</em></div>
          <div class="track-ref-tag" id="trackRefTag">Order #CE-000000</div>
        </div>
        <button class="orders-close" id="trackClose">×</button>
      </div>
      <div id="trackMap"></div>
      <div class="track-info-bar">
        <div class="track-stat">
          <div class="track-stat-val" id="trackDist">—</div>
          <div class="track-stat-lbl">Distance Away</div>
        </div>
        <div class="track-stat">
          <div class="track-stat-val" id="trackEta">—</div>
          <div class="track-stat-lbl">Est. Arrival</div>
        </div>
        <div class="track-stat">
          <div class="track-stat-val" id="trackStatus">In Transit</div>
          <div class="track-stat-lbl">Status</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ PROXIMITY ALERT ══ -->
  <div class="prox-alert" id="proxAlert">
    <button class="prox-dismiss" onclick="document.getElementById('proxAlert').classList.remove('show')">×</button>
    <div class="prox-alert-title">
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-5-5.917V5a1 1 0 10-2 0v.083A6 6 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      🚚 Courier Is Nearby!
    </div>
    <div class="prox-alert-body" id="proxAlertBody">Your courier is approximately 1 mile away. Please prepare to receive
      your order.</div>
  </div>

  <!-- ══ COURIER CHAT FAB ══ -->
  <button class="chat-fab" id="chatFab" title="Chat with Courier">
    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>
    <span class="chat-fab-badge" id="chatFabBadge" style="display:none">1</span>
  </button>

  <!-- CHAT WINDOW -->
  <div class="chat-window" id="chatWindow">
    <div class="chat-win-head">
      <div class="chat-courier-info">
        <div class="chat-avatar" id="chatCourierAvatar">J</div>
        <div>
          <div class="chat-courier-name" id="chatCourierName">Courier</div>
          <div class="chat-courier-status"><span class="online-dot"></span>Online · En Route</div>
        </div>
      </div>
      <button class="chat-win-close" id="chatWinClose">×</button>
    </div>
    <div class="chat-msgs" id="chatMsgs"></div>
    <div class="chat-input-row">
      <input type="text" class="chat-input" id="chatInput" placeholder="Type a message…">
      <button class="chat-send" id="chatSend">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
        </svg>
      </button>
    </div>
  </div>

`;

document.head.insertAdjacentHTML('beforeend', '<style>' + _cartCss + '</style>');
document.body.insertAdjacentHTML('beforeend', _cartHtml);

    // ══ CART SYSTEM ══
    // Collect product data from dynamically created cards
    function getProductData(cardLinkEl) {
      const name = cardLinkEl.getAttribute('data-name') || '';
      const cat = decodeURIComponent(cardLinkEl.getAttribute('data-cat') || '');
      const dbId = cardLinkEl.getAttribute('data-db-id');
      const imgSrc = cardLinkEl.querySelector('img.product-photo')?.src || '';
      const stocks = cardLinkEl.getAttribute('data-stocks') || '0';
      const desc = decodeURIComponent(cardLinkEl.getAttribute('data-desc') || '');
      const sku = decodeURIComponent(cardLinkEl.getAttribute('data-sku') || '');
      return { id: dbId || name.replace(/\s+/g, '-').toLowerCase(), name, price: 'Request Quote', cat, imgSrc, basePrice: 0, stocks, desc, sku };
    }

    let cart = JSON.parse(localStorage.getItem('ceCart') || '[]');

    function saveCart() { localStorage.setItem('ceCart', JSON.stringify(cart)); }

    function updateBadge() {
      const total = cart.reduce((s, i) => s + i.qty, 0);
      const badge = document.getElementById('cartBadge');
      badge.textContent = total;
      if (total > 0) { badge.classList.add('visible'); }
      else { badge.classList.remove('visible'); }
      document.getElementById('cartItemCount').textContent = total + (total === 1 ? ' item' : ' items');
    }

    function formatPrice(n) {
      return '₱' + n.toLocaleString('en-PH');
    }

    function renderCart() {
      const container = document.getElementById('cartItems');
      const empty = document.getElementById('cartEmpty');
      const foot = document.getElementById('cartFoot');

      // Clear existing rendered items (keep empty state)
      container.querySelectorAll('.cart-item, .cart-sep').forEach(el => el.remove());

      if (cart.length === 0) {
        empty.style.display = 'flex';
        foot.style.display = 'none';
        updateBadge();
        return;
      }

      empty.style.display = 'none';
      foot.style.display = 'block';

      let subtotal = 0;
      cart.forEach((item, idx) => {
        subtotal += item.basePrice * item.qty;
        const el = document.createElement('div');
        el.className = 'cart-item';
        el.dataset.idx = idx;
        el.innerHTML = `
          <div class="cart-item-img">
            <img src="${item.imgSrc}" alt="${item.name}" loading="lazy">
          </div>
          <div class="cart-item-info">
            <div class="cart-item-cat">${item.cat}</div>
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-price">${item.price}</div>
            <div class="cart-item-controls">
              <button class="qty-btn" onclick="changeQty(${idx},-1)">−</button>
              <span class="qty-val">${item.qty}</span>
              <button class="qty-btn" onclick="changeQty(${idx},1)">+</button>
            </div>
          </div>
          <button class="cart-item-remove" onclick="removeItem(${idx})" title="Remove">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>`;
        container.appendChild(el);
      });

      const vat = Math.round(subtotal * 0.12);
      const total = subtotal + vat;
      document.getElementById('cartSubtotal').textContent = formatPrice(subtotal);
      document.getElementById('cartVat').textContent = formatPrice(vat);
      document.getElementById('cartTotal').textContent = formatPrice(total);
      updateBadge();
    }

    function openCart() {
      document.getElementById('cartDrawer').classList.add('open');
      document.getElementById('cartOverlay').classList.add('open');
      document.body.style.overflow = 'hidden';
      renderCart();
    }

    function closeCart() {
      document.getElementById('cartDrawer').classList.remove('open');
      document.getElementById('cartOverlay').classList.remove('open');
      document.body.style.overflow = '';
    }

    function addDynamicToCart(cardLink, btn) {
      const data = getProductData(cardLink);
      // Block adding out-of-stock items
      if (Number(data.stocks) <= 0) {
        toast(`${data.name} is currently out of stock. Check back soon!`, 'err');
        return;
      }
      const existing = cart.find(i => i.id === data.id);
      if (existing) {
        existing.qty++;
        toast(`${data.name} — quantity updated (×${existing.qty})`, 'ok');
      } else {
        cart.push({ ...data, qty: 1 });
        toast(`${data.name} added to cart`, 'ok');
      }
      saveCart();
      updateBadge();

      // Visual feedback on button
      const orig = btn.innerHTML;
      btn.classList.add('added');
      btn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Added!`;
      setTimeout(() => {
        btn.classList.remove('added');
        btn.innerHTML = orig;
      }, 1600);
    }

    function changeQty(idx, delta) {
      cart[idx].qty += delta;
      if (cart[idx].qty <= 0) { cart.splice(idx, 1); }
      saveCart();
      renderCart();
    }

    function removeItem(idx) {
      const name = cart[idx].name;
      cart.splice(idx, 1);
      saveCart();
      renderCart();
      toast(`${name} removed from cart`, 'err');
    }

    function clearCart() {
      cart = [];
      saveCart();
      renderCart();
      toast('Cart cleared', 'err');
    }

    // Event listeners for cart
    document.getElementById('navCartBtn').addEventListener('click', openCart);
    document.getElementById('cartCloseBtn').addEventListener('click', closeCart);
    document.getElementById('cartOverlay').addEventListener('click', closeCart);
    document.getElementById('btnClearCart').addEventListener('click', clearCart);
    document.getElementById('btnCheckout').addEventListener('click', () => {
      closeCart();
      setTimeout(openCheckout, 80);
    });

    // Wire up static Add to Cart buttons (if any are left outside dynamic container)
    // (We handled the dynamic ones in renderProducts)

    // Init badge
    updateBadge();

    /* ══ CHECKOUT MODAL SYSTEM ══ */
    let coStep = 1;
    let coTermsAccepted = false;
    let coPaymentMethod = 'cod';

    const paymentLabels = {
      cod: 'Cash on Delivery',
      gcash: 'GCash / Maya',
      bank: 'Bank Transfer',
      quote: 'Request Invoice'
    };

    const timeSlotLabels = {
      morning: 'Morning (8AM–12PM)',
      afternoon: 'Afternoon (12PM–5PM)',
      anytime: 'Any Time'
    };

    function openCheckout() {
      if (!cart || cart.length === 0) {
        toast('Your cart is empty. Add items first.', 'err');
        return;
      }
      coStep = 1;
      coTermsAccepted = false;
      coPaymentMethod = 'cod';
      document.getElementById('coCheck').classList.remove('checked');
      document.getElementById('coCheckIcon').style.display = 'none';

      // Pre-fill user info
      const u = ceUser || {};
      document.getElementById('coFullName').value = u.name || '';
      document.getElementById('coEmail').value = u.email || '';
      document.getElementById('coPhone').value = u.phone || '';

      // Set min delivery date (tomorrow)
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      document.getElementById('coDelivDate').min = tomorrow.toISOString().split('T')[0];
      document.getElementById('coDelivDate').value = '';

      renderCoItems();
      updateCoSteps();
      updateCoFoot();

      document.getElementById('coSuccess').classList.remove('show');
      document.querySelector('.co-body').style.display = '';
      document.getElementById('coFoot').style.display = '';
      document.querySelector('.co-steps').style.display = '';

      document.getElementById('checkoutModal').classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeCheckout() {
      document.getElementById('checkoutModal').classList.remove('open');
      document.body.style.overflow = '';
    }

    document.getElementById('coClose').addEventListener('click', closeCheckout);
    document.getElementById('checkoutModal').addEventListener('click', e => {
      if (e.target === document.getElementById('checkoutModal')) closeCheckout();
    });

    function renderCoItems() {
      const list = document.getElementById('coItemsList');
      list.innerHTML = '';
      cart.forEach((item, idx) => {
        const el = document.createElement('div');
        el.className = 'co-item-row';
        el.innerHTML = `
          <div class="co-item-img"><img src="${item.imgSrc}" alt="${item.name}" loading="lazy"></div>
          <div class="co-item-info">
            <div class="co-item-cat">${item.cat}</div>
            <div class="co-item-name">${item.name}</div>
            <div class="co-item-sku">SKU: ${item.sku || 'N/A'}</div>
          </div>
          <div class="co-item-qty-wrap">
            <button class="co-qty-btn" onclick="coChangeQty(${idx},-1)">−</button>
            <div class="co-qty-val">${item.qty}</div>
            <button class="co-qty-btn" onclick="coChangeQty(${idx},1)">+</button>
          </div>
          <button class="co-item-remove" onclick="coRemoveItem(${idx})" title="Remove">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>`;
        list.appendChild(el);
      });
      updateCoFoot();
    }

    function coChangeQty(idx, delta) {
      cart[idx].qty += delta;
      if (cart[idx].qty <= 0) cart.splice(idx, 1);
      saveCart(); updateBadge();
      if (cart.length === 0) { closeCheckout(); return; }
      renderCoItems();
    }

    function coRemoveItem(idx) {
      cart.splice(idx, 1);
      saveCart(); updateBadge();
      if (cart.length === 0) { closeCheckout(); toast('Cart is now empty.', 'err'); return; }
      renderCoItems();
    }

    function selectPayment(method) {
      coPaymentMethod = method;
      ['cod', 'gcash', 'bank', 'quote'].forEach(m => {
        const el = document.getElementById('payOpt' + m.charAt(0).toUpperCase() + m.slice(1));
        if (el) el.classList.toggle('selected', m === method);
      });
      const notice = document.getElementById('coCodNotice');
      if (notice) notice.style.display = method === 'cod' ? 'flex' : 'none';
    }

    function toggleTerms() {
      coTermsAccepted = !coTermsAccepted;
      const chk = document.getElementById('coCheck');
      const icon = document.getElementById('coCheckIcon');
      chk.classList.toggle('checked', coTermsAccepted);
      icon.style.display = coTermsAccepted ? 'block' : 'none';
      document.getElementById('coBtnNext').disabled = !coTermsAccepted;
    }

    function updateCoSteps() {
      [1, 2, 3, 4].forEach(n => {
        const el = document.getElementById('coStep' + n);
        el.classList.remove('active', 'done');
        if (n < coStep) el.classList.add('done');
        if (n === coStep) el.classList.add('active');
      });
      ['coPanel1', 'coPanel2', 'coPanel3', 'coPanel4'].forEach((id, i) => {
        document.getElementById(id).classList.toggle('active', i + 1 === coStep);
      });
      const back = document.getElementById('coBtnBack');
      const next = document.getElementById('coBtnNext');
      back.style.display = coStep > 1 ? '' : 'none';
      const labels = ['Review Items →', 'Delivery Details →', 'Payment →', 'Place Order'];
      next.textContent = labels[coStep - 1];
      next.disabled = coStep === 4 && !coTermsAccepted;
    }

    function updateCoFoot() {
      const total = cart.reduce((s, i) => s + i.qty, 0);
      document.getElementById('coFootTotal').textContent = 'Request Quote (' + total + ' item' + (total === 1 ? '' : 's') + ')';
    }

    function coValidateStep() {
      if (coStep === 2) {
        const req = ['coFullName', 'coPhone', 'coStreet', 'coBrgy', 'coCity', 'coProvince', 'coDelivDate'];
        for (const id of req) {
          const el = document.getElementById(id);
          if (!el.value.trim()) {
            el.style.borderColor = 'rgba(220,60,60,0.5)';
            el.focus();
            setTimeout(() => el.style.borderColor = '', 2000);
            toast('Please fill in all required delivery fields.', 'err');
            return false;
          }
        }
      }
      return true;
    }

    function buildConfirmPanel() {
      const addr = [
        document.getElementById('coStreet').value,
        document.getElementById('coBrgy').value,
        document.getElementById('coCity').value,
        document.getElementById('coProvince').value,
        document.getElementById('coZip').value
      ].filter(Boolean).join(', ');
      const landmark = document.getElementById('coLandmark').value;
      document.getElementById('coConfirmAddress').innerHTML =
        `<strong>${document.getElementById('coFullName').value}</strong><br>${addr}${landmark ? '<br>Near: ' + landmark : ''}`;
      const dateVal = document.getElementById('coDelivDate').value;
      const dateStr = dateVal ? new Date(dateVal + 'T00:00:00').toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : '—';
      const slot = document.getElementById('coTimeSlot').value;
      document.getElementById('coConfirmDate').innerHTML =
        `<strong>${dateStr}</strong>${slot ? '<br>' + (timeSlotLabels[slot] || slot) : ''}`;
      document.getElementById('coConfirmPayment').innerHTML =
        `<strong>${paymentLabels[coPaymentMethod]}</strong>`;
      document.getElementById('coConfirmContact').innerHTML =
        `<strong>${document.getElementById('coFullName').value}</strong><br>${document.getElementById('coPhone').value}<br>${document.getElementById('coEmail').value || ''}`;
      let html = '';
      cart.forEach(item => {
        html += `<div class="co-summary-row"><span class="co-summary-label">${item.name} × ${item.qty}</span><span class="co-summary-val highlight">Quoted</span></div>`;
      });
      html += `<div class="co-summary-row"><span class="co-summary-label">Delivery Fee</span><span class="co-summary-val">To be confirmed</span></div>`;
      html += `<div class="co-summary-total-row"><span class="co-summary-total-label">Total</span><span class="co-summary-total-val">Quote-Based</span></div>`;
      document.getElementById('coSummaryBody').innerHTML = html;
    }

    function coNext() {
      if (coStep === 4) { submitOrder(); return; }
      if (!coValidateStep()) return;
      coStep++;
      if (coStep === 4) buildConfirmPanel();
      updateCoSteps();
      document.getElementById('coBox').scrollTo({ top: 0, behavior: 'smooth' });
    }

    function coBack() {
      if (coStep <= 1) return;
      coStep--;
      updateCoSteps();
      document.getElementById('coBox').scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function submitOrder() {
      if (!coTermsAccepted) {
        toast('Please accept the terms to continue.', 'err');
        return;
      }
      const btn = document.getElementById('coBtnNext');
      btn.disabled = true;
      btn.textContent = 'Placing Order…';

      const orderData = {
        user_id: (JSON.parse(sessionStorage.getItem('user') || 'null') || {}).id || null,
        full_name: document.getElementById('coFullName').value,
        email: document.getElementById('coEmail').value,
        phone: document.getElementById('coPhone').value,
        street: document.getElementById('coStreet').value,
        barangay: document.getElementById('coBrgy').value,
        city: document.getElementById('coCity').value,
        province: document.getElementById('coProvince').value,
        zip: document.getElementById('coZip').value,
        landmark: document.getElementById('coLandmark').value,
        delivery_date: document.getElementById('coDelivDate').value,
        time_slot: document.getElementById('coTimeSlot').value,
        payment_method: coPaymentMethod,
        notes: document.getElementById('coNotes').value,
        items: cart.map(i => ({ product_id: i.id, product_name: i.name, qty: i.qty, sku: i.sku || '' })),
        total_amount: 0,
        status: 'pending'
      };

      try {
        // orders.php is in the same directory as products.html
        const res = await fetch('orders.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(orderData)
        });
        const json = await res.json();
        const ref = json.order_id
          ? '#CE-' + String(json.order_id).padStart(6, '0')
          : (json.ref || '#CE-' + Date.now().toString().slice(-6));
        showOrderSuccess(ref);
        cart = [];
        saveCart();
        updateBadge();
      } catch (err) {
        console.error('Order submission error:', err);
        // Even on network error, show a reference so UX is graceful
        const ref = '#CE-' + Date.now().toString().slice(-6);
        showOrderSuccess(ref);
        cart = [];
        saveCart();
        updateBadge();
      }
    }

    function showOrderSuccess(ref) {
      document.getElementById('coOrderRef').textContent = ref;
      document.querySelector('.co-body').style.display = 'none';
      document.getElementById('coFoot').style.display = 'none';
      document.querySelector('.co-steps').style.display = 'none';
      document.getElementById('coSuccess').classList.add('show');
      // Save order to local orders list
      saveOrderToHistory(ref);
      setTimeout(closeCheckout, 6000);
    }

    /* ══════════════════════════════════════
       ORDERS SYSTEM
    ══════════════════════════════════════ */
    const STATUS_STEPS = ['pending', 'confirmed', 'transit', 'nearby', 'delivered'];
    const STATUS_LABELS = {
      pending: { label: 'Order Placed', sub: 'Awaiting confirmation from Cal Elite' },
      confirmed: { label: 'Order Confirmed', sub: 'Your order has been accepted' },
      transit: { label: 'Out for Delivery', sub: 'Courier is on the way' },
      nearby: { label: 'Courier Nearby', sub: 'Courier is less than 1 mile away!' },
      delivered: { label: 'Delivered', sub: 'Order has been received' }
    };
    const PILL_CLASS = { pending: 'pill-pending', confirmed: 'pill-confirmed', transit: 'pill-transit', nearby: 'pill-nearby', delivered: 'pill-delivered' };

    let savedOrders = JSON.parse(localStorage.getItem('ceOrders') || '[]');

    function saveOrders() { localStorage.setItem('ceOrders', JSON.stringify(savedOrders)); }

    function saveOrderToHistory(ref) {
      const orderItems = cart.length
        ? cart.map(i => i.name).join(', ')
        : (savedOrders.length ? '' : 'Items from order');
      const order = {
        ref, date: new Date().toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }),
        status: 'pending', items: orderItems,
        courier: 'Juan Dela Cruz', courierInitial: 'J'
      };
      savedOrders.unshift(order);
      saveOrders();
      updateOrdersBadge();
    }

    function updateOrdersBadge() {
      const badge = document.getElementById('ordersBadge');
      const n = savedOrders.length;
      badge.textContent = n;
      n > 0 ? badge.classList.add('visible') : badge.classList.remove('visible');
    }

    function buildTimeline(status) {
      const current = STATUS_STEPS.indexOf(status);
      return STATUS_STEPS.map((s, i) => {
        const isDone = i < current;
        const isActive = i === current;
        const dotClass = isDone ? 'done' : (isActive ? 'active' : '');
        const icon = isDone
          ? `<svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`
          : '';
        return `<div class="tl-step">
          <div class="tl-dot ${dotClass}">${icon}</div>
          <div class="tl-info">
            <div class="tl-label ${!isDone && !isActive ? 'dim' : ''}">${STATUS_LABELS[s].label}</div>
            <div class="tl-sublabel">${STATUS_LABELS[s].sub}</div>
          </div>
        </div>`;
      }).join('');
    }

    function renderOrders() {
      const body = document.getElementById('ordersBody');
      const empty = document.getElementById('ordersEmpty');
      // Clear previous cards
      body.querySelectorAll('.ord-card').forEach(el => el.remove());
      if (savedOrders.length === 0) { empty.style.display = 'flex'; return; }
      empty.style.display = 'none';
      savedOrders.forEach((ord, idx) => {
        const isActive = ord.status !== 'delivered';
        const card = document.createElement('div');
        card.className = 'ord-card';
        card.innerHTML = `
          <div class="ord-card-head">
            <div>
              <div class="ord-ref">${ord.ref}</div>
              <div class="ord-date">${ord.date}</div>
            </div>
            <span class="ord-status-pill ${PILL_CLASS[ord.status]}">${ord.status.toUpperCase()}</span>
          </div>
          <div class="ord-card-body">
            <div class="ord-items-preview">${ord.items || 'Various items'}</div>
            <div class="ord-actions">
              ${isActive ? `<button class="ord-btn" onclick="openTracking(${idx})">
                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Live Track
              </button>
              <button class="ord-btn chat-btn" onclick="goToCourierChat(${idx})">
                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Chat Courier
              </button>` : `<button class="ord-btn" disabled style="opacity:.35;pointer-events:none;">Delivered ✓</button>`}
            </div>
          </div>
          <div class="ord-timeline">${buildTimeline(ord.status)}</div>`;
        body.appendChild(card);
      });
      // Demo: simulate advancement for in-transit orders
      savedOrders.forEach((ord, idx) => {
        if (ord.status === 'pending') {
          setTimeout(() => { savedOrders[idx].status = 'confirmed'; saveOrders(); if (document.getElementById('ordersDrawer').classList.contains('open')) renderOrders(); }, 4000);
        }
        if (ord.status === 'confirmed') {
          setTimeout(() => { savedOrders[idx].status = 'transit'; saveOrders(); if (document.getElementById('ordersDrawer').classList.contains('open')) renderOrders(); showChatFab(idx); }, 10000);
        }
      });
    }

    function openOrders() {
      document.getElementById('ordersOverlay').classList.add('open');
      document.getElementById('ordersDrawer').classList.add('open');
      document.body.style.overflow = 'hidden';
      renderOrders();
    }
    function closeOrders() {
      document.getElementById('ordersOverlay').classList.remove('open');
      document.getElementById('ordersDrawer').classList.remove('open');
      document.body.style.overflow = '';
    }
    document.getElementById('navOrdersBtn').addEventListener('click', openOrders);
    document.getElementById('ordersClose').addEventListener('click', closeOrders);
    document.getElementById('ordersOverlay').addEventListener('click', closeOrders);
    updateOrdersBadge();

    /* ══════════════════════════════════════
       LIVE TRACKING MAP (Leaflet.js)
    ══════════════════════════════════════ */
    let trackMap = null, courierMarker = null, destMarker = null, routeLine = null, trackInterval = null;
    let activeOrderIdx = null;

    // Destination: a fixed pin (Cal Elite store / delivery address placeholder)
    const DEST_LAT = 14.5995, DEST_LNG = 120.9842; // Manila placeholder

    function haversineKm(lat1, lng1, lat2, lng2) {
      const R = 6371;
      const dLat = (lat2 - lat1) * Math.PI / 180, dLng = (lng2 - lng1) * Math.PI / 180;
      const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
      return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function openTracking(idx) {
      activeOrderIdx = idx;
      const ord = savedOrders[idx];
      document.getElementById('trackRefTag').textContent = ord.ref;
      document.getElementById('trackModal').classList.add('open');
      closeOrders();

      // Load Leaflet dynamically
      if (!window.L) {
        const link = document.createElement('link');
        link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
        const s = document.createElement('script');
        s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        s.onload = () => initMap(idx);
        document.head.appendChild(s);
      } else {
        initMap(idx);
      }
    }

    function initMap(idx) {
      if (trackMap) { trackMap.remove(); trackMap = null; }
      if (trackInterval) clearInterval(trackInterval);

      trackMap = L.map('trackMap', { zoomControl: true, attributionControl: false });
      L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 18 }).addTo(trackMap);

      // Start courier ~5 km north of dest
      let courierLat = DEST_LAT + 0.045, courierLng = DEST_LNG + 0.015;

      const courierIcon = L.divIcon({ html: '<div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#b8932a,#d4a843);display:flex;align-items:center;justify-content:center;font-size:16px;border:3px solid rgba(212,168,67,0.5);box-shadow:0 4px 14px rgba(0,0,0,0.5)">🚚</div>', iconSize: [32, 32], className: '' });
      const destIcon = L.divIcon({ html: '<div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#134d27,#22703a);display:flex;align-items:center;justify-content:center;font-size:14px;border:3px solid rgba(74,222,128,0.4)">📍</div>', iconSize: [28, 28], className: '' });

      courierMarker = L.marker([courierLat, courierLng], { icon: courierIcon }).addTo(trackMap).bindPopup('Courier');
      destMarker = L.marker([DEST_LAT, DEST_LNG], { icon: destIcon }).addTo(trackMap).bindPopup('Delivery Address');
      routeLine = L.polyline([[courierLat, courierLng], [DEST_LAT, DEST_LNG]], { color: '#d4a843', weight: 2, dashArray: '6,8' }).addTo(trackMap);
      trackMap.fitBounds([[courierLat, courierLng], [DEST_LAT, DEST_LNG]], { padding: [40, 40] });

      let proxNotified = false;
      trackInterval = setInterval(() => {
        courierLat -= (courierLat - DEST_LAT) * 0.06;
        courierLng -= (courierLng - DEST_LNG) * 0.06;
        courierMarker.setLatLng([courierLat, courierLng]);
        routeLine.setLatLngs([[courierLat, courierLng], [DEST_LAT, DEST_LNG]]);

        const kmAway = haversineKm(courierLat, courierLng, DEST_LAT, DEST_LNG);
        const milesAway = (kmAway * 0.621371).toFixed(2);
        const etaMin = Math.max(1, Math.round(kmAway / 0.25));
        document.getElementById('trackDist').textContent = milesAway + ' mi';
        document.getElementById('trackEta').textContent = etaMin + ' min';

        if (kmAway < 1.6 && !proxNotified) { // ~1 mile
          proxNotified = true;
          showProximityAlert(milesAway, idx);
          savedOrders[idx].status = 'nearby';
          saveOrders();
          document.getElementById('trackStatus').textContent = 'Nearby!';
          document.getElementById('trackStatus').style.color = '#fb923c';
        }
        if (kmAway < 0.04) {
          clearInterval(trackInterval);
          savedOrders[idx].status = 'delivered';
          saveOrders();
          updateOrdersBadge();
          document.getElementById('trackStatus').textContent = 'Delivered ✓';
          document.getElementById('trackStatus').style.color = '#4ade80';
        }
      }, 2000);
    }

    document.getElementById('trackClose').addEventListener('click', () => {
      document.getElementById('trackModal').classList.remove('open');
      if (trackInterval) clearInterval(trackInterval);
    });
    document.getElementById('trackModal').addEventListener('click', e => {
      if (e.target === document.getElementById('trackModal')) {
        document.getElementById('trackModal').classList.remove('open');
        if (trackInterval) clearInterval(trackInterval);
      }
    });

    /* Proximity Alert */
    function showProximityAlert(miles, idx) {
      const pa = document.getElementById('proxAlert');
      document.getElementById('proxAlertBody').textContent =
        `Your courier is approximately ${miles} mile${miles == 1 ? '' : 's'} away. Please prepare to receive your order!`;
      pa.classList.add('show');
      // Browser notification if granted
      if (Notification.permission === 'granted') {
        new Notification('🚚 Courier Nearby!', { body: `Your delivery is only ${miles} miles away!`, icon: '/LOGO/CalElite.png' });
      } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(p => {
          if (p === 'granted') new Notification('🚚 Courier Nearby!', { body: `Your delivery is only ${miles} miles away!` });
        });
      }
      showChatFab(idx);
      setTimeout(() => pa.classList.remove('show'), 12000);
    }

    /* ══════════════════════════════════════
       COURIER CHAT  —  Customer side
       Backend: Supabase REST + Realtime
       Table: chat_messages (order_ref, sender, message, sent_at)
    ══════════════════════════════════════ */
    const SB_URL = 'https://pdqhbxtxvxrwtkvymjlm.supabase.co';
    const SB_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U';

    let chatOrderIdx = null;
    let unreadChatCount = 0;
    let chatRealtimeSub = null;     // active Realtime subscription

    function fmtChatTime(ts) {
      if (!ts) return '';
      try {
        return new Date(ts).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
      } catch (e) { return ''; }
    }

    /* ── Fetch all messages for an order ref ── */
    async function fetchMessages(orderRef) {
      const res = await fetch(
        `${SB_URL}/rest/v1/chat_messages?order_ref=eq.${encodeURIComponent(orderRef)}&order=sent_at.asc`,
        { headers: { apikey: SB_KEY, Authorization: `Bearer ${SB_KEY}` } }
      );
      if (!res.ok) return [];
      return await res.json();
    }

    /* ── Insert a new message ── */
    async function insertMessage(orderRef, sender, message) {
      await fetch(`${SB_URL}/rest/v1/chat_messages`, {
        method: 'POST',
        headers: {
          apikey: SB_KEY,
          Authorization: `Bearer ${SB_KEY}`,
          'Content-Type': 'application/json',
          Prefer: 'return=minimal'
        },
        body: JSON.stringify({ order_ref: orderRef, sender, message })
      });
    }

    /* ── Render messages in the chat window ── */
    function renderCustomerChat(msgs) {
      const container = document.getElementById('chatMsgs');
      if (!container) return;
      if (!msgs || msgs.length === 0) {
        container.innerHTML = `<div style="text-align:center;font-family:'DM Mono',monospace;font-size:8px;color:var(--smoke);opacity:.35;padding:30px 10px;line-height:1.7">Waiting for your courier to connect…<br>They'll reply shortly.</div>`;
      } else {
        container.innerHTML = msgs.map(m => {
          const isMine = m.sender === 'customer';
          const cls = isMine ? 'me' : 'them';
          const who = !isMine
            ? `<div style="font-size:7px;letter-spacing:1px;opacity:.4;margin-bottom:3px;font-family:'DM Mono',monospace">COURIER</div>`
            : '';
          return `<div class="chat-msg ${cls}">${who}<div>${m.message}</div><div class="chat-msg-time">${fmtChatTime(m.sent_at)}</div></div>`;
        }).join('');
      }
      container.scrollTop = container.scrollHeight;
    }

    /* ── Subscribe to Realtime updates for an order ref ── */
    function subscribeRealtime(orderRef) {
      // Disconnect any existing subscription first
      if (chatRealtimeSub) {
        chatRealtimeSub.unsubscribe();
        chatRealtimeSub = null;
      }

      const ws = new WebSocket(
        `wss://pdqhbxtxvxrwtkvymjlm.supabase.co/realtime/v1/websocket?apikey=${SB_KEY}&vsn=1.0.0`
      );

      ws.onopen = () => {
        // Join the realtime channel filtered to this order_ref
        ws.send(JSON.stringify({
          topic: `realtime:public:chat_messages:order_ref=eq.${orderRef}`,
          event: 'phx_join',
          payload: { config: { broadcast: { self: false }, presence: { key: '' } } },
          ref: null
        }));
      };

      ws.onmessage = async (evt) => {
        try {
          const data = JSON.parse(evt.data);
          if (data.event === 'INSERT' && data.payload?.record?.sender === 'courier') {
            // New courier message → refresh chat
            const msgs = await fetchMessages(orderRef);
            if (document.getElementById('chatWindow').classList.contains('open')) {
              renderCustomerChat(msgs);
            } else {
              unreadChatCount++;
              const badge = document.getElementById('chatFabBadge');
              badge.textContent = unreadChatCount;
              badge.style.display = 'flex';
            }
          }
        } catch (e) { }
      };

      ws.onerror = () => { };  // silent — fallback is not needed
      chatRealtimeSub = ws;
    }

    /* ── Show FAB ── */
    function showChatFab(idx) {
      chatOrderIdx = idx;
      const fab = document.getElementById('chatFab');
      fab.classList.add('visible');
      const ord = savedOrders[idx];
      if (ord) {
        document.getElementById('chatCourierName').textContent = ord.courier || 'Courier';
        document.getElementById('chatCourierAvatar').textContent = ord.courierInitial || 'C';
      }
    }

    /* ── Open chat window ── */
    async function goToCourierChat(idx) {
      chatOrderIdx = idx;
      showChatFab(idx);
      unreadChatCount = 0;
      const badge = document.getElementById('chatFabBadge');
      badge.style.display = 'none';
      document.getElementById('chatWindow').classList.add('open');

      const ord = savedOrders[idx];
      const orderRef = ord?.ref;
      if (!orderRef) return;

      // Load & render existing messages
      const msgs = await fetchMessages(orderRef);
      renderCustomerChat(msgs);

      // Subscribe for live updates
      subscribeRealtime(orderRef);

      setTimeout(() => document.getElementById('chatInput').focus(), 50);
    }

    /* ── Send message ── */
    async function sendCustomerMsg() {
      if (chatOrderIdx === null) return;
      const input = document.getElementById('chatInput');
      const text = input.value.trim();
      if (!text) return;
      const orderRef = savedOrders[chatOrderIdx]?.ref;
      if (!orderRef) return;
      input.value = '';
      await insertMessage(orderRef, 'customer', text);
      // Refresh messages after sending
      const msgs = await fetchMessages(orderRef);
      renderCustomerChat(msgs);
    }

    /* ── FAB click → open inline chat ── */
    document.getElementById('chatFab').addEventListener('click', () => {
      const idx = chatOrderIdx !== null ? chatOrderIdx : (savedOrders.length > 0 ? 0 : null);
      if (idx !== null) goToCourierChat(idx);
    });
    document.getElementById('chatWinClose').addEventListener('click', () => {
      document.getElementById('chatWindow').classList.remove('open');
    });
    document.getElementById('chatSend').addEventListener('click', sendCustomerMsg);
    document.getElementById('chatInput').addEventListener('keydown', e => {
      if (e.key === 'Enter') sendCustomerMsg();
    });



