{{--
    x-brand-logo — canonical SafeWash brand component.

    Props (all forwarded to x-brand.logo):
      size         xs | sm | md (default) | lg | xl
      withWordmark true (default) | false
      stacked      false (default) | true — stacks icon above text
      class        extra classes on the wrapper <span>
--}}
<x-brand.logo {{ $attributes }} />
