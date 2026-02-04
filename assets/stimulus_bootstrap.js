import { startStimulusApp } from '@symfony/stimulus-bundle';
import FormCollectionController from './controllers/form_collection_controller.js';
import TomSelectController from './controllers/tom-select_controller.js';

const app = startStimulusApp();
app.register('form-collection', FormCollectionController);
app.register('tom-select', TomSelectController);
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
