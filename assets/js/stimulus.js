import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';
import { startStimulusApp } from '@symfony/stimulus-bridge';

export const adminApplication = startStimulusApp();

const definitions = definitionsFromContext(
    require.context(
        '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
        true,
        /\.[jt]sx?$/
    )
);

definitions.forEach((definition) => {
    definition.identifier = `admin-${definition.identifier}`;
});
adminApplication.load(definitions);