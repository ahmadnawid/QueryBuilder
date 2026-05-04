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
 * AMD module for Query Builder toolbar interactions.
 *
 * @module     report_querybuilder/toolbar
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        init: function(params) {
            var queries  = params.queries || {};
            var select   = document.getElementById('loadqueryselect');
            var textarea = document.getElementById('advsql');
            var editbtn  = document.getElementById('editquerybtn');
            var delbtn   = document.getElementById('deletequerybtn');
            var editid   = document.getElementById('editloadquery');
            var delid    = document.getElementById('deleteloadquery');

            if (!select || !textarea) {
                return;
            }

            select.addEventListener('change', function() {
                var id = select.value;
                if (id && queries[id]) {
                    textarea.value = queries[id].querytext;
                }
                if (editbtn)  { editbtn.disabled  = !id; }
                if (delbtn)   { delbtn.disabled    = !id; }
                if (editid)   { editid.value  = id; }
                if (delid)    { delid.value   = id; }
            });
        }
    };
});
