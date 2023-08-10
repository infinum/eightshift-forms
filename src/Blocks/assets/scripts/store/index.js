import { register, createReduxStore } from '@wordpress/data';

export const FORMS_STORE_NAME = `eightshift-forms`;

// Set default store state.
const DEFAULT_STATE = {
	syncDialog: {},
	isSyncDialogOpen: false,
};

// Define selectors - only getters.
const selectors = {
	getSyncDialog(state) {
		return state?.syncDialog ?? DEFAULT_STATE.syncDialog;
	},
	getIsSyncDialogOpen(state) {
		return state?.isSyncDialogOpen ?? DEFAULT_STATE.isSyncDialogOpen;
	},
};

// Define actions - getters and setters.
const actions = {
	setSyncDialog(syncDialog) {
		return {
			type: 'SET_SYNC_DIALOG',
			syncDialog,
		};
	},
	setIsSyncDialogOpen(isSyncDialogOpen) {
		return {
			type: 'SET_IS_SYNC_DIALOG_OPEN',
			isSyncDialogOpen,
		};
	},
};

// Define reducers - only setters.
const reducer = ( state = DEFAULT_STATE, action ) => { // eslint-disable-line consistent-return
	switch (action.type) {
		case 'SET_SYNC_DIALOG': {
			return {
				...state,
				syncDialog: action.syncDialog,
			};
		}
		case 'SET_IS_SYNC_DIALOG_OPEN': {
			return {
				...state,
				isSyncDialogOpen: action.isSyncDialogOpen,
			};
		}
	}
};

register(createReduxStore(FORMS_STORE_NAME, {
	selectors,
	actions,
	reducer,
}));
