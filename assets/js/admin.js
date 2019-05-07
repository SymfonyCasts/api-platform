import React from 'react';
import ReactDOM from 'react-dom';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import { HydraAdmin, hydraClient, fetchHydra as baseFetchHydra } from '@api-platform/admin';
import authProvider from './authProvider';
import { Route, Redirect } from 'react-router-dom';

const entrypoint = 'http://localhost:8000/api'; // Change this by your own entrypoint
const fetchHeaders = {'Authorization': `Bearer ${localStorage.getItem('token')}`};
const fetchHydra = (url, options = {}) => baseFetchHydra(url, {
        ...options,
    headers: new Headers(fetchHeaders),
});
const dataProvider = api => hydraClient(api, fetchHydra);
const apiDocumentationParser = entrypoint =>
parseHydraDocumentation(entrypoint, {
    headers: new Headers(fetchHeaders),
}).then(
    ({ api }) => ({ api }),
    result => {
    const { api, status } = result;

    if (status === 401) {
        return Promise.resolve({
            api,
            status,
            customRoutes: [
            <Route path="/" render={() => <Redirect to="/login" />} />,
    ],
    });
    }

    return Promise.reject(result);
}
);

ReactDOM.render(
<HydraAdmin
    apiDocumentationParser={apiDocumentationParser}
    authProvider={authProvider}
    entrypoint={entrypoint}
    dataProvider={dataProvider}
/>,
document.getElementById('api-platform-admin'));
