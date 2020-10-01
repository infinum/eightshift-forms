import ServerSideRender from '@wordpress/server-side-render';

export const BasicCaptchaEditor = (props) => {
  const {
    attributes,
    attributes: {
      blockFullName,
    },
  } = props;

  console.log(attributes);

  return (
    <ServerSideRender
      block={blockFullName}
      attributes={attributes}
    />
  );
};
