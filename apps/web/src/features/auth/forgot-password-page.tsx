import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';

export function ForgotPasswordPage() {
  const { resetPassword, error, clearError } = useAuth();
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await resetPassword(email);
      setSent(true);
    } catch {
      // error is set by useAuth
    } finally {
      setLoading(false);
    }
  };

  if (sent) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-muted/50 px-4">
        <Card className="w-full max-w-md">
          <CardHeader className="text-center">
            <CardTitle className="text-2xl">Email Enviado</CardTitle>
            <CardDescription>
              Verifique sua caixa de entrada e siga as instrucoes para redefinir sua senha.
            </CardDescription>
          </CardHeader>
          <CardFooter className="justify-center">
            <Link to="/login" className="text-sm text-primary hover:underline">
              Voltar para o login
            </Link>
          </CardFooter>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-muted/50 px-4">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <CardTitle className="text-2xl">Recuperar Senha</CardTitle>
          <CardDescription>
            Digite seu email e enviaremos um link para redefinir sua senha.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && (
              <div className="rounded-md bg-destructive/10 px-4 py-3 text-sm text-destructive">
                {error}
              </div>
            )}
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                placeholder="seu@email.com"
                value={email}
                onChange={(e) => { setEmail(e.target.value); clearError(); }}
                required
              />
            </div>
            <Button type="submit" className="w-full" disabled={loading}>
              {loading ? 'Enviando...' : 'Enviar link'}
            </Button>
          </form>
        </CardContent>
        <CardFooter className="justify-center">
          <Link to="/login" className="text-sm text-primary hover:underline">
            Voltar para o login
          </Link>
        </CardFooter>
      </Card>
    </div>
  );
}
