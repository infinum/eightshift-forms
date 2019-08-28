import classnames from 'classnames';

export const WrapperEditor = (props) => {
  const {
    children,
    attributes: {
      styleContentWidth,
    },
  } = props;

  const wrapperMainClass = 'field';

  const wrapperClass = classnames([
    wrapperMainClass,
    `${wrapperMainClass}__width--${styleContentWidth}`,
  ]);

  return (
    <div className={wrapperClass}>
      {children}
    </div>
  );
};
