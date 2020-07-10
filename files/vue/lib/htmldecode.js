const htmldecode = (s) => {
    const div = document.createElement('div');
    div.innerHTML = s;
    return div.innerText || div.textContent;
};

// RG 促销信息转移
const rg_promotion_htmldecode = (s) => {
    const div = document.createElement('div');
    div.innerHTML = s;
    return div.innerHTML;
};

window.gs_htmldecode = htmldecode;
window.rg_promotion_htmldecode = rg_promotion_htmldecode;
