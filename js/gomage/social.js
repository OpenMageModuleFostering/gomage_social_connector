/**
 * GoMage Social Connector Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.4.0
 * @since        Class available since Release 1.1.0
 */

GomageSocialClass = Class.create({

    config: null,
    overlay: null,

    initialize: function (config) {
        this.overlay = $('gomage-social-overlay');
        if (!this.overlay) {
            var element = $$('body')[0];
            this.overlay = $(document.createElement('div'));
            this.overlay.id = 'gomage-social-overlay';
            document.body.appendChild(this.overlay);
            var offsets = element.cumulativeOffset();
               window.onload = function(){
                   $('gs-email').focus();
                   var wrapper = GomageSocialClass.elementsByClass('wrapper');
                   if(wrapper){

                           GomageSocialClass.overlay.setStyle({
                               'height': wrapper.getHeight() + 'px',
                               'width': wrapper.getWidth() + 'px'
                           });

                   }
               }
            this.overlay.setStyle({
                'top': offsets[1] + 'px',
                'left': offsets[0] + 'px',
                'width': element.offsetWidth + 'px',
                'height': screen.height + 'px',
                'position': 'absolute',
                'display': 'none',
                'zIndex': '2000'

             });
         }
    },


    sendEmail : function(url) {
        var gsForm = new VarienForm('gs-validate-detail', true);
        var params = this.getFormData();
        if (gsForm.validator && gsForm.validator.validate()) {
            $('gs-please-wait').show();
            $('gs-message').innerHTML = '';
            parameters: params;

            var request = new Ajax.Request(url, {
                method : 'POST',
                parameters : params,
                loaderArea : false,
                onSuccess : function(transport) {
                    var response = eval('(' + (transport.responseText || false)
                        + ')');
                    if(response.error){
                        $('gs-message').innerHTML = response.error;
                    }
                    if(response.success){
                        $('gs-popup-content').hide();
                        window.location.replace(location.href.replace('#_=_', ''));
                    }
                    if(response.redirect){
                        window.location.replace(response.redirect);
                    }
                    $('gs-please-wait').hide();

                }
            });

        }
    },

     id_window: function() {
    var elements = window.parent.document.getElementsByClassName('dialog');
    for (i = 0; i < elements.length; i++){
        id = elements[i].firstChild.id;
        break;
    }
   return id;

},
    unsGsProfile: function(url) {
        var request = new Ajax.Request(url, {
                method : 'POST',
                loaderArea : false
        });
    },
    elementsByClass: function(name) {
        var elements = window.parent.document.getElementsByClassName(name);
        for (i = 0; i < elements.length; i++){
            obj = elements[i];
            break;
        }
        return obj;
    },

    getFormData:function(){
        var form_data = $('gs-form').serialize(true);
        for (var key in form_data){
                form_data[key] = GlcUrl.encode(form_data[key]);
        }

        return form_data;
    },

    createWindow : function (title,width,height)
    {
    win = new Window({
    className:'dialog-gs magento',
    title: title,
    width:width,
    height:height,
        maximizable:false,
        minimizable:false,
        resizable:false,
        draggable:false,
        closeOnEsc: false,
        showEffect:Effect.BlindDown,
        hideEffect:Effect.BlindUp,
        showEffectOptions: {duration: 0.4},
        hideEffectOptions: {duration: 0.4}}

    );
     win.setZIndex(5000);
     return win;
    }




});

var GlcUrl = {

    encode : function (string) {
        return escape(this._utf8_encode(string));
    },

    decode : function (string) {
        return this._utf8_decode(unescape(string));
    },

    _utf8_encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    _utf8_decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }
};