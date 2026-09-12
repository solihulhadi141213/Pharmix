// Run: node tests/ConditionSendUiTest.js (no browser or network required).
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');
const elements = new Map();
const requests = [];
const refreshed = [];
class Element {
    constructor() { this.events = {}; this.props = {}; this.markup = 'Kirim'; }
    on(name, callback) { this.events[name] = callback; return this; }
    prop(key, value) { if (value === undefined) return this.props[key]; this.props[key] = value; return this; }
    html(value) { if (value === undefined) return this.markup; this.markup = value; return this; }
    text(value) { this.markup = value; return this; }
    empty() { this.markup = ''; return this; }
    append() { return this; }
    addClass() { return this; }
    attr(key) { return this.props[key]; }
    find() { return $('#dismiss'); }
    serialize() { return 'diagnosis_code=code-two'; }
    modal(action) { if (action === 'hide') this.events['hide.bs.modal']({ preventDefault() {} }); }
}
function $(selector) {
    if (selector instanceof Element) return selector;
    if (!elements.has(selector)) elements.set(selector, new Element());
    return elements.get(selector);
}
$.ajax = options => {
    const request = { options, abort() { this.aborted = true; } };
    requests.push(request);
    return request;
};
const source = fs.readFileSync(path.join(__dirname, '../_Page/Kunjungan/Kunjungan.js'), 'utf8');
const handler = source.slice(source.indexOf('    // Preview dan pengiriman diagnosis yang sudah tersimpan.'), source.lastIndexOf('});'));
vm.runInNewContext(handler, { $, showToast() {}, ShowAttachment(type, id) { refreshed.push([type, id]); } });
const modal = $('#ModalKirimCondition');
const button = $('#TombolKirimCondition');
const form = $('#ProsesKirimCondition');
function open(code) {
    const trigger = new Element();
    trigger.props['data-id'] = code;
    modal.events['show.bs.modal']({ relatedTarget: trigger });
}
function submit() { form.events.submit.call(form, { preventDefault() {} }); }
open('code-one');
assert.strictEqual(button.prop('disabled'), true);
assert.strictEqual(requests[0].options.data.diagnosis_code, 'code-one');
modal.events['hide.bs.modal']({ preventDefault() {} });
open('code-two');
requests[0].options.success({ status: 'success', eligible: true, html: 'stale preview' });
assert.strictEqual(button.prop('disabled'), true, 'Stale response enabled send');
requests[1].options.success({ status: 'success', eligible: false, html: 'not eligible' });
submit();
assert.strictEqual(requests.length, 2, 'Ineligible submit reached backend');
modal.events['hide.bs.modal']({ preventDefault() {} });
open('code-two');
requests[2].options.success({ status: 'success', eligible: true, html: 'valid preview' });
assert.strictEqual(button.prop('disabled'), false);
submit();
submit();
assert.strictEqual(requests.length, 4, 'Duplicate submit was not blocked');
assert.strictEqual(requests[3].options.url, '_Page/Condition/ProsesKirimCondition.php');
let prevented = false;
modal.events['hide.bs.modal']({ preventDefault() { prevented = true; } });
assert.strictEqual(prevented, true, 'Modal closed during send');
requests[3].options.success({ status: 'success', id_kunjungan: 42, message: 'Sent' });
requests[3].options.complete();
assert.deepStrictEqual(refreshed, [['Condition', 42]]);
assert.strictEqual($('#dismiss').prop('disabled'), false);
open('code-two');
requests[4].options.success({ status: 'success', eligible: true, html: 'valid preview' });
submit();
requests[5].options.error({});
requests[5].options.complete();
assert.strictEqual(button.prop('disabled'), true, 'Ambiguous failure enabled immediate resend');
assert.strictEqual($('#dismiss').prop('disabled'), false);
console.log('PASS: preview state, stale response, eligibility, duplicate submit, busy modal, refresh and request failure.');
