import $ from 'jquery';
import 'selectize/dist/js/selectize';

const userSelectElements = [].slice.call(document.querySelectorAll('[data-pcb-user-picker]'));

userSelectElements.map(function (userSelectEl: HTMLElement) {
    console.info("Initialising user select " + userSelectEl);
    $(userSelectEl).selectize({
        valueField: "account_id",
        labelField: "username",
        searchField: ["username", "email", "id"],
        create: false,
        closeAfterSelect: true,
        placeholder: "Start typing...",
        // openOnFocus: true,
        render: {
            option: function (item, escape) {
                return (
                    `<div class="option">#${escape(item.account_id)}: ${escape(item.username)} <span class="text-muted">(${escape(item.email)})</span></div>`
                );
            },
        },
        load: function (query: string, callback: Function) {
            if (!query.length) return callback();
            $.get('/panel/api/accounts?query=' + query)
                .then((res) => {
                    callback(res.data);
                })
        },
    });
});
