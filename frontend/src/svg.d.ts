
declare module '*.svg?react' {
  import * as React from 'react';

  const SVGComponent: React.ForwardRefExoticComponent<
    React.SVGProps<SVGSVGElement> &
    React.RefAttributes<SVGSVGElement>
  >;

  export default SVGComponent;
}