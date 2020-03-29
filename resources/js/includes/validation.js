export const validate = (className) => {
    if(className) {
        var fields = document.getElementsByClassName(className)
        var isValid = true
        var required = []

        for(let i = 0 ; i < fields.length ; i++) {
            if(fields[i].value == '') {
                required[i] = fields[i].getAttribute('title')
                isValid = false
            }
        }

        const returnObj = {
            valid: isValid,
            required: required
        }
        return returnObj
    }
}

export const makeid = (length) => {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
       result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
 }