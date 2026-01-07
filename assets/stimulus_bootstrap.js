import { startStimulusApp } from '@symfony/stimulus-bundle';
import FormCollectionController from './controllers/form_collection_controller.js';

const app = startStimulusApp();
app.register('form-collection', FormCollectionController);
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
