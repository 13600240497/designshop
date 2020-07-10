// 根据语种获取 config 文件里面的所有字段
const get_languages_by_code = (langPackage, code) => {
    const newLangPackage = {};
    Object.keys(langPackage).map(key => {
        if (langPackage[key] && langPackage[key][code]) {
            newLangPackage[key] = langPackage[key][code];
        } else {
            newLangPackage[key] = '';
        }
    });
    return newLangPackage;
};
window.get_languages_by_code = get_languages_by_code;
