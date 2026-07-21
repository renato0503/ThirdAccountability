import { Controller, Post, Body, HttpException, HttpStatus } from '@nestjs/common';
import * as admin from 'firebase-admin';
import { Public } from '../../common/decorators/public.decorator';

@Controller('seed')
export class SeedController {
  @Public()
  @Post('admin')
  async seedAdmin(@Body() body: { seedKey: string }) {
    const expectedKey = process.env.SEED_KEY || 'gestao3setor-seed-key-2026';
    if (body.seedKey !== expectedKey) {
      throw new HttpException('Chave de seed invalida', HttpStatus.FORBIDDEN);
    }

    const uid = 'wFBPza3O3CRouLoybF2lHregGb52';
    const email = 'gestor.renatorosa@gmail.com';
    const name = 'Renato Rosa';
    const role = 'ADMIN_GERAL';

    try {
      // Set custom claims
      await admin.auth().setCustomUserClaims(uid, {
        role,
        institution_id: null,
      });

      // Create/update Firestore doc
      const userRef = admin.firestore().collection('users').doc(uid);
      await userRef.set(
        {
          uid,
          email,
          name,
          photoURL: '',
          role,
          institutionId: null,
          ativo: true,
          createdAt: admin.firestore.FieldValue.serverTimestamp(),
          updatedAt: admin.firestore.FieldValue.serverTimestamp(),
        },
        { merge: true },
      );

      const userRecord = await admin.auth().getUser(uid);

      return {
        message: 'Admin seed concluido com sucesso',
        uid: userRecord.uid,
        email: userRecord.email,
        claims: userRecord.customClaims,
      };
    } catch (err: any) {
      throw new HttpException(
        `Erro ao seed admin: ${err.message}`,
        HttpStatus.INTERNAL_SERVER_ERROR,
      );
    }
  }
}
