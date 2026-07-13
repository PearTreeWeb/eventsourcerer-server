import { Controller } from '@hotwired/stimulus';
import {DataTable} from 'simple-datatables';
import('simple-datatables/dist/style.min.css');


export default class extends Controller {
    connect() {
        new DataTable(this.element)
    }
}
