import apiFetch from '@wordpress/api-fetch';
import { registerStore } from '@wordpress/data';

const MAILCHIMP_FETCH_SEGMENTS_STORE = 'eightshift-forms/mailchimp-get-segments';

const registerCustomStore = () => {
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

  registerStore(MAILCHIMP_FETCH_SEGMENTS_STORE, {
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
        const segments = yield actions.receiveSegmentsAction('/eightshift-forms/v1/mailchimp-fetch-segments', listId);
        return actions.setSegments({ [listId]: segments });
      },
    },
  });
};

registerCustomStore();

export { MAILCHIMP_FETCH_SEGMENTS_STORE };
