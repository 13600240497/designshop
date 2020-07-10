module.exports = function (path, base = '') {
    try {
        const module = require(base + path)
        return module
    } catch (err) {
        return function ({ ctx }) {
            ctx.body = err.toString();
        }
    }
}
