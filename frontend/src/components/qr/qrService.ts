export type QRType = "clock-in" | "clock-out";

export interface QRTokenResponse {
  token: string;
  expiresIn: number;
}

export async function generateQRToken(type: QRType): Promise<QRTokenResponse> {
  await new Promise((r) => setTimeout(r, 1000));
  return { token: `${type}-${crypto.randomUUID()}`, expiresIn: 60 };
}
