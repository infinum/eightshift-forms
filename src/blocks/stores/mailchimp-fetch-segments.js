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
    receiveSegments(path) {

      return {
        type: 'RECEIVE_SEGMENTS',
        path,
      };
    },
  };

  registerStore(MAILCHIMP_FETCH_SEGMENTS_STORE, {
    reducer(state = { segments: {} }, action) {

      switch (action.type) {
        case 'SET_SEGMENTS':
          return {
            ...state,
            segments: action.segments,
          };
        default:
      }

      return state;
    },

    actions,

    selectors: {
      receiveSegments(state) {
        const { segments } = state;
        return segments;
      },
    },

    controls: {
      RECEIVE_SEGMENTS(action) {
        return apiFetch({ path: action.path });
      },
    },

    resolvers: {
      * receiveSegments(listId) {
        const segments = yield actions.receiveSegments(`/eightshift-forms/v1/mailchimp-fetch-segments?list-id=${listId}`);
        return actions.setSegments(segments);
      },
    },
  });
};

registerCustomStore();

export { MAILCHIMP_FETCH_SEGMENTS_STORE };
