@if ($none_supported)
  <div class="alert alert-danger" role="alert">
    <strong>No supported CI system was detected!</strong> BuildSize currently supports CircleCI, with more coming soon!
  </div>

@endif

<ul>
  @if ($results['circle'])
    <li>
      <strong>CircleCI detected</strong>. BuildSize will automatically track all build artifacts,
      as long as they are correctly configured on CircleCI.
      <a href="https://circleci.com/docs/1.0/build-artifacts/">See CircleCI's docs</a>.
    </li>
  @endif
  @if ($results['circle2'])
    <li>
      <strong>CircleCI detected</strong>. BuildSize will automatically track all build artifacts,
      as long as they are correctly configured on CircleCI.
      <a href="https://circleci.com/docs/2.0/artifacts/#artifacts-overview">See CircleCI's docs</a>.
    </li>
  @endif
  @if ($results['travis'])
    <li>
      <strong>Travis detected</strong>. Unfortunately BuildSize does not support Travis yet. Coming soon!
    </li>
  @endif
</ul>