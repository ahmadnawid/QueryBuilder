// amd/src/analyze.js
define(['core/ajax', 'core/str', 'core/notification'], function(Ajax, Str, Notification) {

    return {
        init: function() {

            var btn      = document.getElementById('analyzebtn');
            var closebtn = document.getElementById('explainclosebtn');
            var panel    = document.getElementById('explainpanel');
            var warnbox  = document.getElementById('explainwarnings');
            var tablebox = document.getElementById('explaintable');
            var spinner  = document.getElementById('analyzespinner');

            if (!btn) {
                return;
            }

            if (closebtn) {
                closebtn.addEventListener('click', function() {
                    panel.style.display = 'none';
                });
            }

            btn.addEventListener('click', function() {
                var sql = document.getElementById('advsql').value.trim();

                if (!sql || sql === 'SELECT ...') {
                    Notification.alert('', M.util.get_string('enter_sql', 'report_querybuilder'));
                    return;
                }

                btn.disabled          = true;
                spinner.style.display = 'inline-block';
                panel.style.display   = 'block';
                warnbox.innerHTML     = '';
                tablebox.innerHTML    = '<em>Running EXPLAIN, please wait...</em>';

                // Load all strings first, then call external function
                Str.get_strings([
                    {key: 'explain_error',       component: 'report_querybuilder'},
                    {key: 'explain_warnings',     component: 'report_querybuilder'},
                    {key: 'explain_no_warnings',  component: 'report_querybuilder'},
                    {key: 'explain_node',         component: 'report_querybuilder'},
                    {key: 'explain_relation',     component: 'report_querybuilder'},
                    {key: 'explain_index',        component: 'report_querybuilder'},
                    {key: 'explain_rows',         component: 'report_querybuilder'},
                    {key: 'explain_cost',         component: 'report_querybuilder'},
                    {key: 'explain_filter',       component: 'report_querybuilder'},
                    {key: 'analyze_query',        component: 'report_querybuilder'},
                ]).then(function(strings) {

                    var strError    = strings[0];
                    var strWarnings = strings[1];
                    var strNowarn   = strings[2];
                    var strNode     = strings[3];
                    var strRelation = strings[4];
                    var strIndex    = strings[5];
                    var strRows     = strings[6];
                    var strCost     = strings[7];
                    var strFilter   = strings[8];
                    var strAnalyze  = strings[9];

                    // Call the Moodle External API
                    Ajax.call([{
                        methodname: 'report_querybuilder_analyze_query',
                        args:       {sql: sql},
                    }])[0].then(function(data) {

                        btn.disabled          = false;
                        spinner.style.display = 'none';
                        btn.textContent       = strAnalyze;

                        // Warnings box
                        if (data.warnings && data.warnings.length > 0) {
                            var whtml = '<div class="mb-2"><strong>' + strWarnings + '</strong></div>'
                                + '<ul class="list-group">';
                            data.warnings.forEach(function(w) {
                                var cls = w.level === 'danger'
                                    ? 'list-group-item list-group-item-danger'
                                    : 'list-group-item list-group-item-warning';
                                whtml += '<li class="' + cls + '">&#9888; ' + w.message + '</li>';
                            });
                            whtml += '</ul>';
                            warnbox.innerHTML = whtml;
                        } else {
                            warnbox.innerHTML = '<div class="alert alert-success">&#10004; ' + strNowarn + '</div>';
                        }

                        // Plan table
                        var thtml = '<table class="table table-sm table-bordered generaltable">';
                        thtml += '<thead class="table-dark"><tr>'
                            + '<th>' + strNode     + '</th>'
                            + '<th>' + strRelation + '</th>'
                            + '<th>' + strIndex    + '</th>'
                            + '<th>' + strRows     + '</th>'
                            + '<th>' + strCost     + '</th>'
                            + '<th>' + strFilter   + '</th>'
                            + '</tr></thead><tbody>';

                        data.steps.forEach(function(step) {
                            var lnode = step.node_type ? step.node_type.toLowerCase() : '';

                            var rowclass = '';
                            if (step.warnings && step.warnings.length > 0) {
                                var hasdan = step.warnings.some(function(w) { return w.level === 'danger'; });
                                rowclass = hasdan ? 'table-danger' : 'table-warning';
                            }

                            var indent = '';
                            for (var i = 0; i < step.depth; i++) {
                                indent += '<span style="display:inline-block;width:18px;">&#8627;</span>';
                            }

                            var noindex = ['sort', 'hash', 'nested loop', 'merge join', 'hash join', 'aggregate'];
                            var isnoindexnode = noindex.some(function(t) { return lnode.indexOf(t) !== -1; });

                            var indexcell = step.index
                                ? '<span class="badge bg-success">' + step.index + '</span>'
                                : isnoindexnode
                                    ? '<span class="text-muted">—</span>'
                                    : '<span class="badge bg-danger">(none)</span>';

                            var nodecell = indent + step.node_type;
                            if (lnode.indexOf('seq scan') !== -1) {
                                nodecell = indent + '<span class="badge bg-danger me-1">SEQ SCAN</span>';
                            } else if (lnode.indexOf('index') !== -1) {
                                nodecell = indent + '<span class="badge bg-success me-1">INDEX</span> '
                                    + step.node_type.replace(/index scan|index only scan|bitmap index scan/i, 'Scan').trim();
                            }

                            thtml += '<tr class="' + rowclass + '">'
                                + '<td>' + nodecell + '</td>'
                                + '<td>' + (step.relation  || '') + '</td>'
                                + '<td>' + indexcell + '</td>'
                                + '<td>' + (step.rows_est   !== null ? Number(step.rows_est).toLocaleString() : '') + '</td>'
                                + '<td>' + (step.cost_total !== null ? step.cost_total : '') + '</td>'
                                + '<td>' + (step.filter     || '') + '</td>'
                                + '</tr>';
                        });

                        thtml += '</tbody></table>';
                        tablebox.innerHTML = thtml;
                        panel.scrollIntoView({behavior: 'smooth', block: 'nearest'});

		}).catch(function(err) {
    btn.disabled          = false;
    spinner.style.display = 'none';
    var errmsg = err.message || err.error || JSON.stringify(err);
    tablebox.innerHTML    = '<div class="alert alert-danger">'
        + strError + ' ' + errmsg + '</div>';
});

                }).catch(function() {
                    btn.disabled          = false;
                    spinner.style.display = 'none';
                });
            });
        }
    };
});
