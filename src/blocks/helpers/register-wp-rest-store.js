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
    setSegments(segments) {
      return {
        type: 'SET_SEGMENTS',
        segments,
      };
    },
    receiveSegmentsAction(path, args) {
      return {
        type: 'RECEIVE_SEGMENTS',
        path,
        args,
      };
    },
  };

  registerStore(routeUri, {
    reducer(state = { segments: {} }, action) {

      switch (action.type) {
        case 'SET_SEGMENTS':
          return {
            segments: {
              ...state.segments || {},
              ...action.segments,
            },

          };
        default:
      }

      return state;
    },

    actions,

    selectors: {
      receiveSegments(state, args) {
        const { segments } = state;
        return segments[generateKeyFromArgs(args)] ?? {};
      },
    },

    controls: {
      RECEIVE_SEGMENTS(action) {
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
      * receiveSegments(args) {
        const segments = yield actions.receiveSegmentsAction(`/${routeUri}`, args);
        return actions.setSegments({ [generateKeyFromArgs(args)]: segments });
      },
    },
  });
};
