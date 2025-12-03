import Fastify, { FastifyInstance } from 'fastify';

export function buildApp(): FastifyInstance {
  const app = Fastify({
    logger: process.env['NODE_ENV'] !== 'test', 
  });

  app.get('/', async () => ({ status: 'ok' }));
  app.get('/health', async () => ({ health: true }));

  return app;
}


