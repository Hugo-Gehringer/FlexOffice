
import { startStimulusApp } from '@symfony/stimulus-bundle';
import DeskModalController from './controllers/desk_modal_controller.js';
import DeskFormController from './controllers/desk_form_controller.js';
import FlowbiteController from './controllers/flowbite_controller.js';

const app = startStimulusApp();
app.register('desk-modal', DeskModalController);
app.register('desk-form', DeskFormController);
app.register('flowbite', FlowbiteController);
