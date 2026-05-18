/**
 * login.js
 * Script yang akan di load di halaman login
 * Usahakan menggunakan vanilla javascript (native) tanpa library
 * (semisal jQuery dsb) sehingga load halaman login cepat
 */

(function() {
    window.addEventListener("DOMContentLoaded", function() {
        var btnLogin = getId('btn-login');
        var msgLogin = getId('msg-login');

        btnLogin.onclick = function(me) {
            var user = document.getElementsByName('username')[0].value;
            var pwd  = document.getElementsByName('password')[0].value;
                pwd  = pwd ? md5(pwd) : '';
            var data = 'username=' + user + '&password=' + pwd;
            ajax('loginout.php', function(result) {
                var res = JSON.parse(result);
                if (res.success) {
                    msgLogin.style.display = 'none';
                    window.location = 'main.php';
                    var el = getId('loading-login');
                    if (el.classList.contains('is-hidden')) {
                        el.classList.remove('is-hidden');
                    }
                    var fl = getId('form-login');
                    fl.style.display = 'none';
                    return 1;
                } else {
                    msgLogin.innerHTML = res.msg;
                    fadeIn(msgLogin);
                }
            }, data);
            me.preventDefault();
        };
    });

    function getId(id) {
        return document.getElementById(id);
    }

    // simple ajax, example: ajax(url,function(data) {} )
    // jika ada parameter ke-3 -> POST, parameter ke-3 datanya
    function ajax(B, A) {
        this.bindFunction = function(E, D) {
            return function() {
                return E.apply(D, [D])
            }
        };
        this.stateChange = function(D) {
            if (this.request.readyState == 4) { this.callbackFunction(this.request.responseText) }
        };
        this.getRequest = function() {
            if (window.ActiveXObject) {
                return new ActiveXObject("Microsoft.XMLHTTP");
            } else {
                if (window.XMLHttpRequest) {
                    return new XMLHttpRequest()
                }
            }
            return false
        };
        this.postBody = (arguments[2] || "");
        this.callbackFunction = A;
        this.url = B;
        this.request = this.getRequest();
        if (this.request) {
            var C = this.request;
            C.onreadystatechange = this.bindFunction(this.stateChange, this);
            if (this.postBody !== "") {
                C.open("POST", B, true);
                C.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                C.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
            } else { C.open("GET", B, true) }
            C.send(this.postBody)
        }
    };

    // fade out
    function fadeOut(el) {
        el.style.opacity = 1;
        (function fade() {
            if ((el.style.opacity -= .1) < 0) {
                el.style.display = 'none';
                el.classList.add('is-hidden');
            } else {
                requestAnimationFrame(fade);
            }
        })();
    }

    // fade in
    function fadeIn(el, display) {
        if (el.classList.contains('is-hidden')) {
            el.classList.remove('is-hidden');
        }
        el.style.opacity = 0;
        el.style.display = display || "block";

        (function fade() {
            var val = parseFloat(el.style.opacity);
            if (!((val += .1) > 1)) {
                el.style.opacity = val;
                requestAnimationFrame(fade);
            }
        })();
    }
}());

//# sourceURL=app/login.js
