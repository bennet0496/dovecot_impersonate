rcmail.addEventListener('init', function() {
    rcmail.set_env("plugin.dovecot_impersonate.showredacted", false);

    const btn = document.createElement("a");
    btn.className = "unredact";
    btn.role = "button";
    btn.onclick = function (event) {
        if (rcmail.env["plugin.dovecot_impersonate.showredacted"] !== false) {
            document.querySelectorAll("link").forEach(function (link) {
                if (link.href.includes("dovecot_impersonate_redact")) {
                    link.disabled = false;
                }
            })

            rcmail.set_env("plugin.dovecot_impersonate.showredacted", false);
            document.querySelector(".menu a.unredact").innerHTML = "<span class='inner'>Show data</span>";
        } else {
            document.querySelectorAll("link").forEach(function (link) {
                if (link.href.includes("dovecot_impersonate_redact")) {
                    link.disabled = true;
                }
            })

            rcmail.set_env("plugin.dovecot_impersonate.showredacted", true);
            document.querySelector(".menu a.unredact").innerHTML = "<span class='inner'>Hide data</span>";
        }
    }
    btn.innerHTML = "<span class='inner'>Show data</span>"

    document.querySelector("#taskmenu").appendChild(btn);
})