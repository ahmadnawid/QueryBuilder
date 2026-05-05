// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AMD module for Query Builder explain plan analyzer.
 *
 * @module     report_querybuilder/analyze
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/str', 'core/notification', 'core/templates'], function(Ajax, Str, Notification, Templates) {

    return {
        init: function() {

            var btn = document.getElementById('analyzebtn');
            var closebtn = document.getElementById('explainclosebtn');
            var panel = document.getElementById('explainpanel');
            var warnbox = document.getElementById('explainwarnings');
            var tablebox = document.getElementById('explaintable');
            var spinner = document.getElementById('analyzespinner');

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

                if (!sql) {
                    Notification.alert('Query required', 'Please enter a SQL query to analyze.');
                    return;
                }

                btn.disabled = true;
                spinner.style.display = 'inline-block';
                panel.style.display = 'block';
                warnbox.innerHTML = '';

                // Show loading state via template.
                Templates.render('report_querybuilder/explain_loading', {
                    message: 'Running EXPLAIN, please wait...'
                }).then(function(html) {
                    tablebox.innerHTML = html;
                    return;
                }).catch(Notification.exception);

                Str.get_strings([
                    {key: 'explain_error', component: 'report_querybuilder'},
                    {key: 'explain_warnings', component: 'report_querybuilder'},
                    {key: 'explain_no_warnings', component: 'report_querybuilder'},
                    {key: 'explain_node', component: 'report_querybuilder'},
                    {key: 'explain_relation', component: 'report_querybuilder'},
                    {key: 'explain_index', component: 'report_querybuilder'},
                    {key: 'explain_rows', component: 'report_querybuilder'},
                    {key: 'explain_cost', component: 'report_querybuilder'},
                    {key: 'explain_filter', component: 'report_querybuilder'},
                    {key: 'analyze_query', component: 'report_querybuilder'}
                ]).then(function(strings) {

                    var strError = strings[0];
                    var strWarnings = strings[1];
                    var strNowarn = strings[2];
                    var strNode = strings[3];
                    var strRelation = strings[4];
                    var strIndex = strings[5];
                    var strRows = strings[6];
                    var strCost = strings[7];
                    var strFilter = strings[8];
                    var strAnalyze = strings[9];

                    return Ajax.call([{
                        methodname: 'report_querybuilder_analyze_query',
                        args: {sql: sql}
                    }])[0].then(function(data) {

                        btn.disabled = false;
                        spinner.style.display = 'none';
                        btn.textContent = strAnalyze;

                        // Render warnings via template.
                        var warningsdata = {
                            'has_warnings': data.warnings && data.warnings.length > 0,
                            'warnings_heading': strWarnings,
                            'no_warnings_message': strNowarn,
                            warnings: (data.warnings || []).map(function(w) {
                                return {
                                    message: w.message,
                                    is_danger: w.level === 'danger'
                                };
                            })
                        };

                        // eslint-disable-next-line promise/no-nesting
                        Templates.render('report_querybuilder/explain_warnings', warningsdata)
                            .then(function(html) {
                                warnbox.innerHTML = html;
                                return;
                            }).catch(Notification.exception);

                        // Build steps data for table template.
                        var steps = (data.steps || []).map(function(step) {
                            var lnode = step.node_type ? step.node_type.toLowerCase() : '';
                            var rowclass = '';

                            if (step.warnings && step.warnings.length > 0) {
                                var hasdan = step.warnings.some(function(w) {
                                    return w.level === 'danger';
                                });
                                rowclass = hasdan ? 'table-danger' : 'table-warning';
                            }

                            var indent = '';
                            for (var i = 0; i < step.depth; i++) {
                                indent += '<span style="display:inline-block;width:18px;">&#8627;</span>';
                            }

                            var nodecell = indent + step.node_type;
                            if (lnode.indexOf('seq scan') !== -1) {
                                nodecell = indent + '<span class="badge bg-danger me-1">SEQ SCAN</span>';
                            } else if (lnode.indexOf('index') !== -1) {
                                nodecell = indent + '<span class="badge bg-success me-1">INDEX</span> ' +
                                    step.node_type.replace(/index scan|index only scan|bitmap index scan/i, 'Scan').trim();
                            }

                            var noindex = ['sort', 'hash', 'nested loop', 'merge join', 'hash join', 'aggregate'];
                            var isnoindexnode = noindex.some(function(t) {
                                return lnode.indexOf(t) !== -1;
                            });
                            var indexcell = step.index
                                ? '<span class="badge bg-success">' + step.index + '</span>'
                                : isnoindexnode
                                    ? '<span class="text-muted">&#8212;</span>'
                                    : '<span class="badge bg-danger">(none)</span>';

                            return {
                                row_class: rowclass,
                                node_cell: nodecell,
                                relation: step.relation || '',
                                index_cell: indexcell,
                                rows_formatted: step.rows_est !== null
                                    ? Number(step.rows_est).toLocaleString()
                                    : '',
                                cost_total: step.cost_total !== null ? step.cost_total : '',
                                filter: step.filter || ''
                            };
                        });

                        var tabledata = {
                            col_node: strNode,
                            col_relation: strRelation,
                            col_index: strIndex,
                            col_rows: strRows,
                            col_cost: strCost,
                            col_filter: strFilter,
                            steps: steps
                        };

                        // eslint-disable-next-line promise/no-nesting
                        Templates.render('report_querybuilder/explain_table', tabledata)
                            .then(function(html) {
                                tablebox.innerHTML = html;
                                panel.scrollIntoView({behavior: 'smooth', block: 'nearest'});
                                return;
                            }).catch(Notification.exception);

                        return;
                    }).catch(function(err) {
                        btn.disabled = false;
                        spinner.style.display = 'none';
                        var errmsg = err.message || err.error || JSON.stringify(err);
                        Templates.render('report_querybuilder/explain_loading', {
                            message: strError + ' ' + errmsg
                        }).then(function(html) {
                            tablebox.innerHTML = html;
                            return;
                        }).catch(Notification.exception);
                    });
                }).catch(Notification.exception);
            });
        }
    };
});
