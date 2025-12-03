import 'dotenv/config';
import { buildApp } from './app.js';

const app = buildApp();

async function start() {
  try {
    await app.listen({ port: 3000, host: '::' });
    console.log('Server is running on http://localhost:3000');
  } catch (err) {
    app.log.error(err);
    process.exit(1);
  }
}

start();
