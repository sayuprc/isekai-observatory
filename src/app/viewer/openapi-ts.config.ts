import { defineConfig } from '@hey-api/openapi-ts';

export default defineConfig({
  input: '../contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml',
  output: './src/generated',
});
