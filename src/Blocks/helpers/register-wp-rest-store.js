import apiFetch from '@wordpress/api-fetch';
import { registerStore } from '@wordpress/data';
import crypto from 'crypto';

/**
 * Generates a unique hash (to be used as key) from argument data. This is to ensure each call with different arguments
 * is properly stored / retrieved.
 *
 * Tries to be smart in determining which algo is used and has a relatively ugly fallback if for some reason none of
 * the acceptable hashing algorithms are available in Node.
 *
 * @param {array} args Route query arguments.
 * @return {string}
 */
const generateKeyFromArgs = (args) => {
	const acceptableAlgos = [
		'sha256',
		'md5',
	];

	const usedAlgo = acceptableAlgos.filter((algo) => {
		return crypto.getHashes().includes(algo);
	})[0] ?? '';

	const hash = usedAlgo ? crypto.createHash(usedAlgo).update(JSON.stringify(args)).digest('hex') : JSON.stringify(args);
	return hash;
};

/**
 * Registers a custom store for a custom WP Rest route. All GET parameters should be passed on
 * to selector and ultimately to route itself.
 */
export const registerWpRestStore = (routeUri) => {
	const actions = {
		setResponse(response) {
			return {
				type: 'SET_RESPONSE',
				response,
			};
		},
		receiveResponseAction(path, args) {
			return {
				type: 'RECEIVE_RESPONSE',
				path,
				args,
			};
		},
	};

	registerStore(routeUri, {
		reducer(state = { response: {} }, action) {

			switch (action.type) {
				case 'SET_RESPONSE':
					return {
						response: {
							...state.response || {},
							...action.response,
						},

					};
				default:
			}

			return state;
		},

		actions,

		selectors: {
			receiveResponse(state, args) {
				const { response } = state;
				return response[generateKeyFromArgs(args)] ?? {};
			},
		},

		controls: {
			RECEIVE_RESPONSE(action) {
				const {
					path,
					args,
				} = action;

				const argsAsQueryParams = `${args[0].key}=${args[0].value}`;
				const fullPath = `${path}?${argsAsQueryParams}`;
				return apiFetch({ path: fullPath });
			},
		},

		resolvers: {
			* receiveResponse(args) {
				const response = yield actions.receiveResponseAction(`/${routeUri}`, args);
				return actions.setResponse({ [generateKeyFromArgs(args)]: response });
			},
		},
	});
};
