import apiFetch from '@wordpress/api-fetch';
import { registerStore } from '@wordpress/data';

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
    receiveSegmentsAction(path, listId) {
      return {
        type: 'RECEIVE_SEGMENTS',
        path,
        listId,
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
      receiveSegments(state, listId) {
        const { segments } = state;
        return segments[listId] ?? {};
      },
    },

    controls: {
      RECEIVE_SEGMENTS(action) {
        const path = `${action.path}?list-id=${action.listId}`;
        return apiFetch({ path });
      },
    },

    resolvers: {
      * receiveSegments(listId) {
        const segments = yield actions.receiveSegmentsAction(`/${routeUri}`, listId);
        return actions.setSegments({ [listId]: segments });
      },
    },
  });
};
