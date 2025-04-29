
import { startStimulusApp } from '@symfony/stimulus-bundle';
import FlashMessagesController from './controllers/flash_messages_controller.js';

const app = startStimulusApp();
app.register('flash-messages', FlashMessagesController);
