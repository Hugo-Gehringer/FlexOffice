import { Controller } from '@hotwired/stimulus';
import { initFlowbite } from 'flowbite';

export default class extends Controller {
    connect() {
        initFlowbite();
    }
}